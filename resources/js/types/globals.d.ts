import type { SharedPageData } from '@/types/app-data'

declare global {
    // Reading an undeclared `import.meta.env` key is a type error instead of `any`.
    interface ViteTypeOptions {
        strictImportMetaEnv: unknown
    }

    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string
        readonly VITE_REVERB_APP_KEY: string
        readonly VITE_REVERB_HOST: string
        readonly VITE_REVERB_PORT: string
        readonly VITE_REVERB_SCHEME: string
    }
}

// Types every `usePage()`, `$page`, and `createInertiaApp` with the backend-owned
// shared props that `HandleInertiaRequests` sends on each response.
declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedPageData
    }
}
