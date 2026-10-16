import SidebarProvider from '@/components/ui/sidebar/SidebarProvider.vue'
import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import { defineComponent, h } from 'vue'
import NavMain from '../NavMain.vue'

// `Link` is the one child that cannot render for real here: it needs a live
// Inertia router. Every other child (`UiSidebar*`) is resolved by the shared
// `unplugin-vue-components` config in `vitest.config.ts`, exactly as at runtime,
// which is why the real `SidebarProvider` has to supply the sidebar context.
const linkStub = defineComponent({
    props: {
        href: {
            type: String,
            required: true
        }
    },
    inheritAttrs: false,
    setup(props, { attrs, slots }) {
        return () => h('a', { href: props.href, ...attrs }, slots.default?.())
    }
})

describe('NavMain', () => {
    it('uses centralized item isActive state for aria-current', () => {
        const wrapper = mount(
            defineComponent({
                components: { NavMain, SidebarProvider },
                template: `
                    <SidebarProvider>
                        <NavMain :items="items" />
                    </SidebarProvider>
                `,
                data: () => ({
                    items: [
                        {
                            title: 'Dashboard',
                            href: '/app/dashboard',
                            isActive: true
                        },
                        {
                            title: 'Users',
                            href: '/app/admin/users',
                            isActive: false
                        }
                    ]
                })
            }),
            {
                global: {
                    stubs: {
                        Link: linkStub
                    }
                }
            }
        )

        const links = wrapper.findAll('a')

        expect(links).toHaveLength(2)
        const firstLink = links[0]
        const secondLink = links[1]

        expect(firstLink).toBeDefined()
        expect(secondLink).toBeDefined()
        expect(firstLink?.attributes('aria-current')).toBe('page')
        expect(secondLink?.attributes('aria-current')).toBeUndefined()
    })
})
