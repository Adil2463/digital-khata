import { UserRole } from '@/types/app-data'
import { describe, expect, it } from 'vitest'
import { buildUserFormFields, createUserFormDefaults } from '../user-form-schema'

const fieldByName = (fields: ReturnType<typeof buildUserFormFields>, name: string) => fields.find((field) => field.name === name)

describe('user form schema', () => {
    it('defaults new accounts to the staff role', () => {
        expect(createUserFormDefaults()).toEqual({
            name: '',
            email: '',
            role: UserRole.User,
            password: '',
            password_confirmation: ''
        })
    })

    it('requires a confirmed password only when creating an account', () => {
        const createFields = buildUserFormFields({ isEdit: false, isSelf: false })
        const editFields = buildUserFormFields({ isEdit: true, isSelf: false })

        expect(fieldByName(createFields, 'password')?.required).toBe(true)
        expect(fieldByName(createFields, 'password_confirmation')?.required).toBe(true)
        expect(fieldByName(editFields, 'password')?.required).toBe(false)
        expect(fieldByName(editFields, 'password_confirmation')?.required).toBe(false)
    })

    it('offers owner and staff labels and locks the role when editing yourself', () => {
        const ownFields = buildUserFormFields({ isEdit: true, isSelf: true })
        const role = fieldByName(ownFields, 'role')

        expect(role?.disabled).toBe(true)
        expect(role?.options).toEqual([
            { value: UserRole.Admin, label: 'Owner' },
            { value: UserRole.User, label: 'Staff' }
        ])
        expect(fieldByName(buildUserFormFields({ isEdit: true, isSelf: false }), 'role')?.disabled).toBe(false)
    })
})
