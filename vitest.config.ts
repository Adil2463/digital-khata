import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import AutoImport from 'unplugin-auto-import/vite'
import IconsResolver from 'unplugin-icons/resolver'
import Icons from 'unplugin-icons/vite'
import Components from 'unplugin-vue-components/vite'
import { defineConfig } from 'vitest/config'
import { autoImportOptions, componentAutoImportOptions, iconComponentPrefix, inertiaComponentResolver } from './frontend-auto-import.config.mjs'

export default defineConfig({
    plugins: [
        vue(),
        AutoImport({
            ...autoImportOptions,
            dts: false
        }),
        Icons({
            compiler: 'vue3'
        }),
        // Mirrors `vite.config.ts` so a mounted component resolves `Ui*`, `Base*`,
        // and module components exactly like it does at runtime. Without this a
        // test has to hand-stub every auto-registered child just to render.
        Components({
            ...componentAutoImportOptions,
            dts: false,
            resolvers: [
                inertiaComponentResolver,
                IconsResolver({
                    prefix: iconComponentPrefix
                })
            ]
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url))
        }
    },
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.ts']
    }
})
