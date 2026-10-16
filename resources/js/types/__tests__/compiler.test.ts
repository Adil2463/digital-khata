// @vitest-environment node
import { spawnSync } from 'node:child_process'
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs'
import { createRequire } from 'node:module'
import { join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { expect, it } from 'vitest'

const root = fileURLToPath(new URL('../../../../', import.meta.url))
const require = createRequire(import.meta.url)

it('uses TypeScript 7 semantics while enforcing Vue and backend contracts', () => {
    const cache = join(root, 'node_modules/.cache')
    mkdirSync(cache, { recursive: true })
    const directory = mkdtempSync(join(cache, 'compiler-contract-'))

    try {
        writeFileSync(
            join(directory, 'tsconfig.json'),
            JSON.stringify({
                extends: join(root, 'tsconfig.json'),
                compilerOptions: { incremental: false },
                include: ['./*.ts', './*.vue'],
                exclude: []
            })
        )
        writeFileSync(
            join(directory, 'unicode.ts'),
            `
type Split<S extends string> = S extends \`\${infer Head}\${infer Tail}\` ? [Head, Tail] : never
export const split: Split<'😀abc'> = ['😀', 'abc']
`
        )
        writeFileSync(
            join(directory, 'Child.vue'),
            `
<script setup lang="ts">
import type { UserViewData } from '@/types/app-data'
defineProps<{ user: UserViewData }>()
</script>
<template><span>{{ user.name }}</span></template>
`
        )
        const page = (invalid: boolean) => `
<script setup lang="ts">
import type { UserViewData } from '@/types/app-data'
import Child from './Child.vue'
defineProps<{ user: UserViewData }>()
</script>
<template>
    <Child :user="${invalid ? '42' : 'user'}" />
    <span>{{ user.id.${invalid ? 'toUpperCase' : 'toFixed'}() }}</span>
    <span>{{ user.email_verified_at${invalid ? '.' : '?.'}toUpperCase() }}</span>
</template>
`
        const check = () => {
            const result = spawnSync(
                process.execPath,
                [require.resolve('vue-tsc/bin/vue-tsc.js'), '--noEmit', '--pretty', 'false', '-p', join(directory, 'tsconfig.json')],
                {
                    cwd: root,
                    encoding: 'utf8',
                    timeout: 30000
                }
            )
            if (result.error) throw result.error
            return { status: result.status, output: result.stdout + result.stderr }
        }

        writeFileSync(join(directory, 'Page.vue'), page(false))
        const valid = check()
        expect(valid.status, valid.output).toBe(0)

        writeFileSync(join(directory, 'Page.vue'), page(true))
        const invalid = check()
        expect(invalid.status, invalid.output).toBe(2)
        expect(invalid.output).toMatch(/Page\.vue.*TS2322/)
        expect(invalid.output).toMatch(/Page\.vue.*TS2339/)
        expect(invalid.output).toMatch(/Page\.vue.*TS18047/)
    } finally {
        rmSync(directory, { recursive: true, force: true })
    }
}, 60000)
