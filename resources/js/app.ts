import { createInertiaApp } from '@inertiajs/vue3'
import { breakpointsTailwind, provideSSRWidth } from '@vueuse/core'
import { createPinia } from 'pinia'
import { createApp, createSSRApp, h } from 'vue'
import AppRoot from './components/AppRoot.vue'
import { configureRealtime } from './lib/realtime/config'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Lets Inertia nonce the style elements it injects in the browser (progress bar,
// error modal) so they satisfy the CSP set by `App\Http\Middleware\SecurityHeaders`.
const cspNonce = import.meta.env.SSR ? '' : (document.querySelector<HTMLMetaElement>('meta[name="csp-nonce"]')?.content ?? '')

configureRealtime()

// This file is both the client and the SSR entry. `@inertiajs/vite` rewrites the
// `pages` literal into an `import.meta.glob` resolver, and for the SSR build and
// the dev SSR endpoint it wraps this top-level call into a render function.
createInertiaApp({
    pages: {
        path: './modules',
        extension: '.vue',
        transform: (name) => name.replace(/^modules\//, '')
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    nonce: cspNonce,
    setup({ el, App, props, plugin }) {
        // Server renders and server-rendered roots use the SSR renderer; anything else
        // mounts fresh, so toggling `INERTIA_SSR_ENABLED` never causes a hydration
        // mismatch. Pinia is created per app because the SSR process is long-lived.
        const create = import.meta.env.SSR || el?.hasAttribute('data-server-rendered') === true ? createSSRApp : createApp
        const app = create({ render: () => h(AppRoot, null, () => h(App, props)) })
            .use(plugin)
            .use(createPinia())

        // The server cannot measure a viewport, so media queries resolve against a
        // desktop width there and during hydration, then switch to the real
        // viewport once mounted. Without this, narrow screens hydrate a different
        // sidebar tree than the server rendered and every later `useId()` shifts.
        provideSSRWidth(breakpointsTailwind.lg, app)

        if (el !== null) {
            app.mount(el)
        }

        return app
    },
    progress: {
        color: 'var(--primary)'
    }
})
