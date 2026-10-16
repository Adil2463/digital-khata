import { mount } from '@vue/test-utils'
import { reactiveOmit } from '@vueuse/core'
import { expect, expectTypeOf, it } from 'vitest'
import { defineComponent, h } from 'vue'
import { useForwardedProps, useForwardedPropsEmits } from '../forward-props'

it('preserves required props, defaults, explicit false values, and reactive updates', async () => {
    const Wrapper = defineComponent({
        props: {
            value: { type: String, required: true },
            enabled: Boolean,
            label: { type: String, default: 'Default' },
            optional: String,
            local: String
        },
        setup(props) {
            const forwarded = useForwardedProps(reactiveOmit(props, 'local'))
            expectTypeOf(forwarded.value.value).toEqualTypeOf<string>()
            return () => h('output', JSON.stringify(forwarded.value))
        }
    })
    const wrapper = mount(Wrapper, { props: { value: 'first', enabled: false, local: 'private' } })
    try {
        expect(JSON.parse(wrapper.text())).toEqual({ value: 'first', enabled: false, label: 'Default' })
        await wrapper.setProps({ value: 'second', enabled: true, optional: 'present' })
        expect(JSON.parse(wrapper.text())).toEqual({ value: 'second', enabled: true, label: 'Default', optional: 'present' })
    } finally {
        wrapper.unmount()
    }
})

it('forwards update events once with their typed payload', async () => {
    const Wrapper = defineComponent({
        props: { modelValue: { type: Boolean, required: true } },
        emits: ['update:modelValue'],
        setup(props, { emit }) {
            const forwarded = useForwardedPropsEmits(props, emit)
            return () => h('button', { onClick: () => forwarded.value['onUpdate:modelValue']?.(!forwarded.value.modelValue) })
        }
    })
    const wrapper = mount(Wrapper, { props: { modelValue: false } })
    try {
        await wrapper.trigger('click')
        expect(wrapper.emitted('update:modelValue')).toEqual([[true]])
    } finally {
        wrapper.unmount()
    }
})
