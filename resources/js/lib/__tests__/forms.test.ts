import { describe, expect, it } from 'vitest'
import { defineFormContract, mapInertiaFormErrors } from '../forms'

interface ExampleFormValues {
    name: string
    email: string
}

describe('mapInertiaFormErrors', () => {
    it('maps string-based validation errors to a typed form map', () => {
        const result = mapInertiaFormErrors<ExampleFormValues>({
            name: 'The name field is required.',
            email: 'The email must be valid.',
            ignored: ['array error']
        })

        expect(result).toEqual({
            name: 'The name field is required.',
            email: 'The email must be valid.'
        })
    })
})

describe('defineFormContract', () => {
    it('returns a typed form contract with defaults and fields', () => {
        const contract = defineFormContract<ExampleFormValues>({
            defaults: () => ({
                name: '',
                email: ''
            }),
            fields: () => [
                { name: 'name', label: 'Name', type: 'text', required: true },
                { name: 'email', label: 'Email', type: 'email', required: true }
            ]
        })

        expect(contract.defaults()).toEqual({
            name: '',
            email: ''
        })
        expect(contract.fields().map((field) => field.name)).toEqual(['name', 'email'])
    })
})
