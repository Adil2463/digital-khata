import prettier from 'eslint-config-prettier'
import vue from 'eslint-plugin-vue'
import { defineConfig } from 'eslint/config'
import fs from 'node:fs'
import path from 'node:path'
import tseslint from 'typescript-eslint'
import vueParser from 'vue-eslint-parser'

import { autoImportRestrictedPaths, autoImportRestrictedPatterns, autoImportSourceGlobs } from './frontend-auto-import.config.mjs'

const modulesRoot = path.resolve(process.cwd(), 'resources/js/modules')

const frontendModuleNames = fs.existsSync(modulesRoot)
    ? fs
          .readdirSync(modulesRoot, { withFileTypes: true })
          .filter((entry) => entry.isDirectory())
          .map((entry) => entry.name)
          .sort((left, right) => left.localeCompare(right))
    : []

const baseRestrictedSyntaxSelectors = [
    {
        selector:
            "TSAsExpression[typeAnnotation.type='TSTypeReference'][typeAnnotation.typeName.name='Record'][expression.type='TSAsExpression'][expression.typeAnnotation.type='TSUnknownKeyword']",
        message: 'Avoid `as unknown as Record<string, unknown>`. Refactor the receiving type to accept the source type directly.'
    },
    {
        selector: "VariableDeclarator[id.name='fields'][init.type='ArrayExpression']",
        message:
            'Do not define inline form schema arrays in page/components. Move form schemas to `resources/js/modules/**/forms/*-form-schema.ts` and consume via builder functions.'
    },
    {
        selector:
            "VariableDeclarator[id.name=/^(sidebarNavItems|dashboardPrimaryItems|dashboardFooterItems|marketingPrimaryItems|marketingFooterGroups)$/][init.type='ArrayExpression']",
        message: 'Do not duplicate navigation arrays. Centralize navigation contracts in `resources/js/config/navigation.ts`.'
    }
]

const buildRestrictedSyntaxRule = (extraSelectors = []) => ['error', ...baseRestrictedSyntaxSelectors, ...extraSelectors]

// The `@typescript-eslint` variant is what understands `allowTypeImports`, so a
// shared layer can keep exporting types that consumers import explicitly while
// its runtime exports stay auto-import-only.
const buildNoRestrictedImportsRule = (extraPatterns = []) => [
    'error',
    {
        paths: autoImportRestrictedPaths,
        patterns: [...autoImportRestrictedPatterns, ...extraPatterns]
    }
]

const restrictedImportRules = (extraPatterns = []) => ({
    'no-restricted-imports': 'off',
    '@typescript-eslint/no-restricted-imports': buildNoRestrictedImportsRule(extraPatterns)
})

const crossModuleImportPattern = (moduleName) => ({
    group: frontendModuleNames.filter((candidate) => candidate !== moduleName).map((candidate) => `@/modules/${candidate}/**`),
    message: `Cross-module imports are not allowed in feature module "${moduleName}". Move shared code to a shared/base layer.`
})

const moduleBoundaryConfigs = frontendModuleNames.map((moduleName) => ({
    files: [`resources/js/modules/${moduleName}/**/*.{ts,vue}`],
    rules: restrictedImportRules([crossModuleImportPattern(moduleName)])
}))

// Files inside the auto-imported directories define those symbols; they wire up
// their own siblings with explicit imports rather than depending on auto-import
// resolving back into the directory it is scanning.
const autoImportSourceConfig = {
    files: autoImportSourceGlobs,
    rules: {
        'no-restricted-imports': 'off',
        '@typescript-eslint/no-restricted-imports': 'off'
    }
}

const moduleAutoImportSourceBoundaryConfigs = frontendModuleNames.map((moduleName) => ({
    files: [`resources/js/modules/${moduleName}/composables/**/*.ts`, `resources/js/modules/${moduleName}/helpers/**/*.ts`],
    rules: {
        'no-restricted-imports': 'off',
        '@typescript-eslint/no-restricted-imports': [
            'error',
            {
                patterns: [crossModuleImportPattern(moduleName)]
            }
        ]
    }
}))

const testFileGlobs = ['resources/js/**/__tests__/**/*.ts', 'resources/js/**/*.test.ts']

// Specs must be able to name the real thing: component auto-registration only
// applies to compiled SFC templates, and spying needs a module namespace object.
// Module boundaries are still enforced below.
const testFileConfig = {
    files: testFileGlobs,
    rules: {
        'no-restricted-imports': 'off',
        '@typescript-eslint/no-restricted-imports': 'off'
    }
}

const moduleTestBoundaryConfigs = frontendModuleNames.map((moduleName) => ({
    files: testFileGlobs.map((glob) => glob.replace('resources/js/', `resources/js/modules/${moduleName}/`)),
    rules: {
        '@typescript-eslint/no-restricted-imports': [
            'error',
            {
                patterns: [crossModuleImportPattern(moduleName)]
            }
        ]
    }
}))

const pageDataAccessGuardConfig = {
    files: ['resources/js/modules/**/pages/**/*.{ts,vue}'],
    rules: {
        'no-restricted-syntax': buildRestrictedSyntaxRule([
            {
                selector: "CallExpression[callee.name='fetch']",
                message:
                    'Do not call fetch directly in page files. Use shared API/query composables (`useApiQuery`, `useApiMutation`) via module contracts.'
            }
        ])
    }
}

export default defineConfig(
    vue.configs['flat/essential'],
    tseslint.configs.recommended.map((config) => (config.files ? { ...config, files: [...config.files, '**/*.vue'] } : config)),
    vue.configs['flat/base'],
    {
        files: ['**/*.vue'],
        languageOptions: {
            parser: vueParser,
            parserOptions: {
                parser: { js: 'espree', jsx: 'espree', ts: tseslint.parser, tsx: tseslint.parser },
                ecmaVersion: 2024,
                ecmaFeatures: { jsx: false },
                extraFileExtensions: ['.vue']
            }
        },
        rules: {
            'vue/block-lang': ['error', { script: { lang: ['ts'], allowNoLang: false } }]
        }
    },
    {
        ignores: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'tailwind.config.js',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
            'resources/js/types/app-data.ts',
            'resources/js/types/auto-imports.d.ts',
            'resources/js/types/components.d.ts'
        ]
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'error',
            'no-restricted-syntax': buildRestrictedSyntaxRule(),
            ...restrictedImportRules()
        }
    },
    ...moduleBoundaryConfigs,
    pageDataAccessGuardConfig,
    autoImportSourceConfig,
    ...moduleAutoImportSourceBoundaryConfigs,
    testFileConfig,
    ...moduleTestBoundaryConfigs,
    {
        // Vendored primitives keep explicit imports instead of auto-import, because the
        // shadcn-vue CLI regenerates them and would drop anything auto-import supplies.
        // Every other rule, including unused-variable detection, applies unchanged.
        files: ['resources/js/components/ui/**/*.{ts,vue}'],
        rules: {
            '@typescript-eslint/no-restricted-imports': 'off'
        }
    },
    prettier
)
