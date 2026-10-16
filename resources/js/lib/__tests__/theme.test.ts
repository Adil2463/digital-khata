import { mount } from '@vue/test-utils'
import { readFileSync, readdirSync, statSync } from 'node:fs'
import { join, relative } from 'node:path'
import { describe, expect, it } from 'vitest'

import InputError from '@/components/InputError.vue'
import { appTheme, buttonStyles, sidebarButtonStyles, toastStyles } from '../theme'

const projectRoot = process.cwd()

const sourceExtensions = /\.(css|ts|vue)$/
const stubExtensions = /\.stub$/

const sourceFiles = (directory: string, extensions: RegExp = sourceExtensions): string[] =>
    readdirSync(directory).flatMap((entry) => {
        const path = join(directory, entry)

        if (statSync(path).isDirectory()) {
            return entry === '__tests__' ? [] : sourceFiles(path, extensions)
        }

        return extensions.test(entry) ? [path] : []
    })

// Stubs are the source of generated modules, so they must honor the same contracts as hand-written source.
const isVueSource = (path: string, contents: string): boolean => path.endsWith('.vue') || (path.endsWith('.stub') && contents.includes('<template'))

// Every leaf recipe string in the theme, so a test can assert that no file re-declares one.
const themeRecipes = (value: unknown): string[] => {
    if (typeof value === 'string') {
        return [value]
    }

    if (typeof value !== 'object' || value === null) {
        return []
    }

    return Object.values(value).flatMap(themeRecipes)
}

// Single-utility recipes (`p-1`, `size-4`) are too common to treat as owned by the theme.
const ownedRecipes = [...new Set(themeRecipes(appTheme))].filter((recipe) => recipe.trim().split(/\s+/).length >= 3)

describe('theme contracts', () => {
    it('resolves typed control variants from the canonical recipes', () => {
        expect(buttonStyles()).toContain(appTheme.button.variant.default)
        expect(buttonStyles({ variant: 'destructive', size: 'sm' })).toContain(appTheme.button.variant.destructive)
        expect(sidebarButtonStyles({ variant: 'outline', size: 'lg' })).toContain(appTheme.navigation.sidebarVariant.outline)
        expect(toastStyles('success')).toContain(appTheme.toast.variant.success)
    })

    it('keeps raw visual values in theme.css', () => {
        const themePath = join(projectRoot, 'resources/css/theme.css')
        const candidates = [...sourceFiles(join(projectRoot, 'resources/js')), ...sourceFiles(join(projectRoot, 'resources/css'))].filter(
            (path) => path !== themePath
        )
        const rawColor = /#[\da-f]{3,8}\b|\b(?:rgb|hsl)a?\(/i
        const paletteUtility =
            /\b(?:bg|border|fill|ring|stroke|text)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black)(?:-|\b)/
        const paletteToken =
            /--color-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black)(?:-|\b)/
        const violations = candidates
            .filter((path) => {
                const contents = readFileSync(path, 'utf8')

                return rawColor.test(contents) || paletteUtility.test(contents) || paletteToken.test(contents)
            })
            .map((path) => relative(projectRoot, path))

        expect(violations).toEqual([])
        expect(readFileSync(join(projectRoot, 'resources/css/app.css'), 'utf8')).toContain("@import './theme.css';")
    })

    it('keeps presentation dependencies and local style blocks out of frontend source and generator stubs', () => {
        const candidates = [...sourceFiles(join(projectRoot, 'resources/js')), ...sourceFiles(join(projectRoot, 'stubs'), stubExtensions)]
        const bannedDependency = /@lucide\/vue|class-variance-authority/
        const violations = candidates
            .filter((path) => {
                const contents = readFileSync(path, 'utf8')

                return bannedDependency.test(contents) || (isVueSource(path, contents) && contents.includes('<style'))
            })
            .map((path) => relative(projectRoot, path))

        expect(violations).toEqual([])
    })

    it('does not re-declare a canonical recipe as a literal class list', () => {
        const candidates = [...sourceFiles(join(projectRoot, 'resources/js')), ...sourceFiles(join(projectRoot, 'stubs'), stubExtensions)].filter(
            (path) => path !== join(projectRoot, 'resources/js/lib/theme.ts')
        )
        const violations = candidates.flatMap((path) => {
            const contents = readFileSync(path, 'utf8')

            return ownedRecipes.filter((recipe) => contents.includes(recipe)).map((recipe) => `${relative(projectRoot, path)}: ${recipe}`)
        })

        expect(violations).toEqual([])
    })

    it('runs the tooltip enter animation on mount rather than on data-[state=open]', () => {
        // Reka's tooltip content reports `delayed-open`/`instant-open`, so a
        // `data-[state=open]` enter animation silently never plays.
        expect(appTheme.tooltip).toContain('animate-in')
        expect(appTheme.tooltip).toContain('fade-in-0')
        expect(appTheme.tooltip).toContain('zoom-in-95')
        expect(appTheme.tooltip).not.toContain('data-[state=open]')
        expect(appTheme.tooltip).toContain('data-[state=closed]:animate-out')
    })

    it('suppresses the native focus ring on every recipe that draws its own', () => {
        const focusRingRecipes = [
            buttonStyles(),
            appTheme.field.control,
            appTheme.field.checkbox,
            appTheme.field.radio,
            appTheme.field.switch,
            appTheme.field.tabsTrigger,
            appTheme.select.trigger,
            appTheme.navigation.trigger
        ]

        for (const recipe of focusRingRecipes) {
            expect(recipe).toMatch(/\bfocus-visible:ring-/)
            expect(recipe).toMatch(/\b(?:outline-none|outline-hidden)\b/)
        }
    })

    it('binds the theme in <script setup> instead of naming it inside a template', () => {
        // `vue-tsc` cannot resolve an auto-imported symbol that only ever appears in
        // markup, so every consumer aliases `appTheme` in `<script setup>` first.
        // Reaching for `appTheme.` directly in a template compiles but fails typecheck.
        const candidates = [...sourceFiles(join(projectRoot, 'resources/js')), ...sourceFiles(join(projectRoot, 'stubs'), stubExtensions)]
        const violations = candidates
            .filter((path) => {
                const contents = readFileSync(path, 'utf8')

                if (!isVueSource(path, contents)) {
                    return false
                }

                // Explicit imports are typed even when the theme is only used in markup.
                if (contents.includes("from '@/lib/theme'")) {
                    return false
                }

                return /<template[\s\S]*appTheme\./.test(contents)
            })
            .map((path) => relative(projectRoot, path))

        expect(violations).toEqual([])
    })

    it('renders a themed component through auto-import, exactly as the app does', () => {
        // Guards the auto-import parity between `vite.config.ts` and `vitest.config.ts`:
        // without matching options the theme is undefined at render time in a spec.
        const wrapper = mount(InputError, { props: { message: 'Required.' } })

        expect(wrapper.get('p').classes()).toEqual(appTheme.field.error.split(' '))
    })
})
