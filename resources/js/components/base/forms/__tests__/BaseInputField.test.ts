import { mount } from '@vue/test-utils'
import { expect, it } from 'vitest'
import { defineComponent, h } from 'vue'
import BaseFormRenderer from '../BaseFormRenderer.vue'
import BaseInputField from '../BaseInputField.vue'

it('keeps labels and feedback local when two forms share field names', () => {
    const Wrapper = defineComponent({
        setup: () => () =>
            h(
                'div',
                [1, 2].map(() =>
                    h(BaseFormRenderer<{ name: string }>, {
                        model: { name: '' },
                        fields: [{ name: 'name', label: 'Name', type: 'text', description: 'Enter a name' }],
                        errors: { name: 'Name is required' }
                    })
                )
            )
    })
    const wrapper = mount(Wrapper)
    try {
        const ids = wrapper.findAll('[id]').map((element) => element.attributes('id'))
        expect(new Set(ids).size).toBe(ids.length)
        for (const form of wrapper.findAll('form')) {
            const input = form.get('input')
            expect(form.get('label').attributes('for')).toBe(input.attributes('id'))
            for (const id of input.attributes('aria-describedby')!.split(' ')) {
                expect(form.findAll('[id]').some((element) => element.attributes('id') === id)).toBe(true)
            }
        }
    } finally {
        wrapper.unmount()
    }
})

it('does not emit another submission while processing', async () => {
    const wrapper = mount(BaseFormRenderer<{ name: string }>, { props: { model: { name: '' }, processing: true } })
    try {
        await wrapper.get('form').trigger('submit')
        expect(wrapper.emitted('submit')).toBeUndefined()
        await wrapper.setProps({ processing: false })
        await wrapper.get('form').trigger('submit')
        expect(wrapper.emitted('submit')).toHaveLength(1)
    } finally {
        wrapper.unmount()
    }
})

it('disables every field while the form is processing', () => {
    const wrapper = mount(BaseFormRenderer<{ name: string }>, {
        props: {
            model: { name: 'Saved name' },
            fields: [{ name: 'name', label: 'Name', type: 'text' }],
            processing: true
        }
    })
    try {
        expect(wrapper.get('input').attributes('disabled')).toBeDefined()
    } finally {
        wrapper.unmount()
    }
})

it.each(['tabs', 'multiselect'] as const)('respects disabled options in %s', (type) => {
    const wrapper = mount(BaseInputField<{ choice: string | string[] }>, {
        props: {
            id: 'choice',
            field: { name: 'choice', label: 'Choice', type, options: [{ value: 'locked', label: 'Locked', disabled: true }] },
            modelValue: type === 'tabs' ? 'locked' : []
        }
    })
    try {
        expect(wrapper.get(type === 'tabs' ? '[role="tab"]' : 'option').attributes('disabled')).toBeDefined()
    } finally {
        wrapper.unmount()
    }
})

it.each(['checkbox', 'toggle', 'select', 'multiselect', 'radio', 'tabs', 'file'] as const)('prevents changes to a readonly %s field', (type) => {
    const wrapper = mount(BaseInputField<{ choice: unknown }>, {
        props: {
            id: 'readonly-choice',
            field: { name: 'choice', label: 'Choice', type, readonly: true, options: [{ value: 'one', label: 'One' }] },
            modelValue: null
        }
    })
    try {
        const selector = {
            checkbox: '[role="checkbox"]',
            toggle: '[role="switch"]',
            select: '[role="combobox"]',
            multiselect: 'select',
            radio: '[role="radio"]',
            tabs: '[role="tab"]',
            file: 'input'
        }[type]
        expect(wrapper.get(selector).attributes('disabled')).toBeDefined()
    } finally {
        wrapper.unmount()
    }
})

it('associates a toggle label and field feedback with its control', () => {
    const wrapper = mount(BaseInputField<{ enabled: boolean }>, {
        props: {
            id: 'enabled',
            field: { name: 'enabled', label: 'Enabled', type: 'toggle', description: 'Allow access' },
            modelValue: false,
            error: 'Access is required'
        }
    })
    try {
        const control = wrapper.get('[role="switch"]')
        expect(control.attributes('id')).toBe('enabled')
        expect(control.attributes('aria-invalid')).toBe('true')
        expect(control.attributes('aria-describedby')).toBe('enabled-description enabled-error')
        expect(wrapper.get('#enabled-description').text()).toBe('Allow access')
        expect(wrapper.get('#enabled-error').text()).toBe('Access is required')
    } finally {
        wrapper.unmount()
    }
})

it('renders one checkbox label and preserves its required marker and interaction', async () => {
    const wrapper = mount(BaseInputField<{ remember: boolean }>, {
        props: {
            id: 'remember',
            field: { name: 'remember', label: 'Remember me', type: 'checkbox', required: true },
            modelValue: false
        }
    })
    try {
        expect(wrapper.findAll('label[for="remember"]')).toHaveLength(1)
        expect(wrapper.get('label').text()).toBe('Remember me *')
        await wrapper.get('[role="checkbox"]').trigger('click')
        expect(wrapper.emitted('update:modelValue')).toEqual([[true]])
    } finally {
        wrapper.unmount()
    }
})

it('associates a select label with its focusable trigger', () => {
    const wrapper = mount(BaseInputField<{ role: string }>, {
        props: {
            id: 'role',
            field: { name: 'role', label: 'Role', type: 'select', options: [{ value: 'user', label: 'User' }] },
            modelValue: 'user'
        }
    })
    try {
        expect(wrapper.get('[role="combobox"]').attributes('id')).toBe('role')
        expect(wrapper.get('label').attributes('for')).toBe('role')
    } finally {
        wrapper.unmount()
    }
})
