import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { describe, expect, it } from 'vitest'
import SidebarProvider from '../SidebarProvider.vue'
import { useSidebar } from '../utils'

const SidebarStateProbe = defineComponent({
    setup() {
        const { state, toggleSidebar } = useSidebar()

        return {
            state,
            toggleSidebar
        }
    },
    template: `
        <button type="button" data-testid="toggle" @click="toggleSidebar">toggle</button>
        <div data-testid="state">{{ state }}</div>
    `
})

describe('SidebarProvider', () => {
    it('uses defaultOpen for uncontrolled desktop state and toggles correctly', async () => {
        const wrapper = mount({
            components: {
                SidebarProvider,
                SidebarStateProbe
            },
            template: `
                <SidebarProvider :default-open="false">
                    <SidebarStateProbe />
                </SidebarProvider>
            `
        })

        expect(wrapper.get('[data-testid="state"]').text()).toBe('collapsed')

        await wrapper.get('[data-testid="toggle"]').trigger('click')
        expect(wrapper.get('[data-testid="state"]').text()).toBe('expanded')
    })
})
