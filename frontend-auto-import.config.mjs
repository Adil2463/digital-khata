export const autoImportMessage = 'Manual import is not needed here. This project auto-imports this symbol; remove the import and rely on auto-import.'

export const autoImportDirs = [
    'resources/js/composables/**',
    'resources/js/stores/**',
    'resources/js/lib/**',
    'resources/js/utils/**',
    'resources/js/modules/**/composables/**',
    'resources/js/modules/**/helpers/**'
]

export const autoImportImports = [
    'vue',
    {
        '@inertiajs/vue3': ['usePage', 'useForm', 'useRemember', 'usePoll', 'router', 'Deferred'],
        '@/routes/app': [['default', 'appRoutes']],
        '@/routes/auth': [['default', 'authRoutes']],
        '@/routes/marketing': [['default', 'marketingRoutes']],
        '@/config/breadcrumbs': [
            'buildDashboardBreadcrumbs',
            'buildUsersBreadcrumbs',
            'buildSettingsProfileBreadcrumbs',
            'buildSettingsPasswordBreadcrumbs',
            'buildSettingsAppearanceBreadcrumbs'
        ]
    }
]

// Plugin-agnostic half of the `unplugin-auto-import` contract. `vite.config.ts` and
// `vitest.config.ts` both spread this so a symbol that resolves in a template at
// runtime resolves the same way when a component test mounts that template.
// `vueTemplate` is the load-bearing option here: without it an auto-imported value
// used directly inside `<template>` is undefined at render time.
export const autoImportOptions = {
    vueTemplate: true,
    imports: autoImportImports,
    dirs: autoImportDirs
}

export const componentDirs = ['resources/js/components', 'resources/js/layouts', 'resources/js/modules']

// Plugin-agnostic half of the `unplugin-vue-components` contract. `vite.config.ts`
// and `vitest.config.ts` both spread this so component resolution behaves the same
// when the app runs and when a component test mounts a tree.
export const componentAutoImportOptions = {
    deep: true,
    extensions: ['vue'],
    collapseSamePrefixes: true,
    directoryAsNamespace: true,
    globalNamespaces: ['components'],
    dirs: componentDirs
}

export const iconComponentPrefix = 'Icon'

/** @type {(componentName: string) => { name: string, from: string } | undefined} */
export const inertiaComponentResolver = (componentName) => {
    if (['Link', 'Head'].includes(componentName)) {
        return { name: componentName, from: '@inertiajs/vue3' }
    }

    return undefined
}

export const autoImportRestrictedPaths = [
    {
        name: '@inertiajs/vue3',
        importNames: ['usePage', 'useForm', 'useRemember', 'usePoll', 'router', 'Deferred', 'Head', 'Link'],
        message: autoImportMessage
    },
    {
        name: '@/composables/useAppPage',
        importNames: ['useAppPage', 'useAuthUser'],
        message: autoImportMessage
    },
    {
        name: '@/routes/app',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/routes/auth',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/routes/marketing',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/config/breadcrumbs',
        importNames: [
            'buildDashboardBreadcrumbs',
            'buildUsersBreadcrumbs',
            'buildSettingsProfileBreadcrumbs',
            'buildSettingsPasswordBreadcrumbs',
            'buildSettingsAppearanceBreadcrumbs'
        ],
        message: autoImportMessage
    }
]

// Consumed as `@typescript-eslint/no-restricted-imports` patterns. `allowTypeImports`
// is what makes these safe to apply to every auto-imported directory: auto-import
// only provides runtime values, so `import type { ... }` stays legal everywhere.
export const autoImportRestrictedPatterns = [
    {
        group: ['@/composables/*', '@/composables/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/stores/*', '@/stores/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/lib/*', '@/lib/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/utils/*', '@/utils/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/components/**/*.vue', '@/layouts/**/*.vue'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/modules/**/composables/*', '@/modules/**/composables/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/modules/**/helpers/*', '@/modules/**/helpers/**'],
        message: autoImportMessage,
        allowTypeImports: true
    },
    {
        group: ['@/modules/**/components/*.vue', '@/modules/**/components/**/*.vue'],
        message: autoImportMessage,
        allowTypeImports: true
    }
]

// Files that *define* auto-imported symbols. They import their siblings explicitly
// so the shared layer stays readable and never depends on auto-import resolving
// into itself; only consumers outside these directories rely on auto-import.
export const autoImportSourceGlobs = [
    'resources/js/composables/**/*.ts',
    'resources/js/stores/**/*.ts',
    'resources/js/lib/**/*.ts',
    'resources/js/utils/**/*.ts',
    'resources/js/modules/*/composables/**/*.ts',
    'resources/js/modules/*/helpers/**/*.ts'
]
