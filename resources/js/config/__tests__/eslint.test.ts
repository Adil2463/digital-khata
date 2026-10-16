// @vitest-environment node
import { ESLint } from 'eslint'
import { expect, it } from 'vitest'

const eslint = new ESLint()

it('parses TypeScript in Vue scripts and templates', async () => {
    const results = await eslint.lintText(
        '<script setup lang="ts">defineProps<{ count: number }>()</script><template>{{ (count as number).toFixed() }}</template>',
        { filePath: 'resources/js/modules/users/pages/LintFixture.vue' }
    )

    expect(results.flatMap((result) => result.messages)).toEqual([])
})

it.each([
    ['unused TypeScript variables', 'const unused = 1', 'LintFixture.ts', '@typescript-eslint/no-unused-vars'],
    [
        'unsafe Vue types',
        '<script setup lang="ts">defineProps<{ value: any }>()</script><template>{{ value }}</template>',
        'LintFixture.vue',
        '@typescript-eslint/no-explicit-any'
    ],
    ['untyped Vue scripts', '<script setup>const title = "hello"</script><template>{{ title }}</template>', 'LintFixture.vue', 'vue/block-lang'],
    [
        'cross-module imports',
        'export { default } from "@/modules/settings/pages/Profile.vue"',
        'LintFixture.ts',
        '@typescript-eslint/no-restricted-imports'
    ],
    ['manual auto-imports', 'export { usePage } from "@inertiajs/vue3"', 'LintFixture.ts', '@typescript-eslint/no-restricted-imports'],
    ['direct page requests', 'fetch("/api/v1/me")', 'LintFixture.ts', 'no-restricted-syntax']
])('rejects %s', async (_description, code, filename, rule) => {
    const results = await eslint.lintText(code, { filePath: `resources/js/modules/users/pages/${filename}` })

    expect(results.flatMap((result) => result.messages.map((message) => message.ruleId))).toContain(rule)
})

it('allows explicit imports in shared auto-import sources', async () => {
    const results = await eslint.lintText('import { usePage } from "@inertiajs/vue3"; export const page = usePage()', {
        filePath: 'resources/js/composables/LintFixture.ts'
    })

    expect(results.flatMap((result) => result.messages)).toEqual([])
})
