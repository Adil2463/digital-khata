import { clearApiQueryCache, getApiQueryCacheData, setApiQueryCacheData } from '@/composables/useApiQuery'
import { mount } from '@vue/test-utils'
import { afterEach, expect, it, vi } from 'vitest'
import { defineComponent, h, reactive } from 'vue'
import AppRoot from '../AppRoot.vue'

const pageProps = reactive<{ auth: { user: { id: number } | null }; flash: { message: null; error: null; status: null } }>({
    auth: { user: { id: 1 } },
    flash: { message: null, error: null, status: null }
})
const state = reactive<{ props: typeof pageProps | undefined }>({ props: undefined })
vi.mock('@inertiajs/vue3', () => ({ usePage: () => state }))

afterEach(() => clearApiQueryCache())

it('clears cached account data synchronously on an identity change', () => {
    const Page = defineComponent({
        setup() {
            state.props = pageProps
            return () => h('div')
        }
    })
    const wrapper = mount(AppRoot, { slots: { default: () => h(Page) } })
    try {
        setApiQueryCacheData('account', { private: 'first-user' })
        pageProps.auth.user = null
        expect(getApiQueryCacheData('account')).toBeUndefined()
        pageProps.auth.user = { id: 2 }
        setApiQueryCacheData('account', { private: 'second-user' })
        pageProps.auth.user = { id: 3 }
        expect(getApiQueryCacheData('account')).toBeUndefined()
    } finally {
        wrapper.unmount()
    }
})

it('renders the Inertia page it wraps', () => {
    state.props = pageProps
    const wrapper = mount(AppRoot, { slots: { default: () => h('main', { id: 'page' }) } })
    try {
        expect(wrapper.find('#page').exists()).toBe(true)
    } finally {
        wrapper.unmount()
    }
})
