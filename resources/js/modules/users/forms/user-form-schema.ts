import type { FormValuesFromData } from '@/lib/forms'
import type { CreateUserData } from '@/types/app-data'
import { UserRole } from '@/types/app-data'

export type UserFormValues = FormValuesFromData<
    CreateUserData,
    {
        role: CreateUserData['role'] | ''
        password_confirmation: string
    }
>

export interface UserFormFieldOptions {
    isEdit: boolean
    isSelf: boolean
}

export const createUserFormDefaults = (): UserFormValues => ({
    name: '',
    email: '',
    role: UserRole.User,
    password: '',
    password_confirmation: ''
})

export const userFormContract = defineFormContract<UserFormValues>({
    defaults: createUserFormDefaults,
    fields: () => buildUserFormFields({ isEdit: false, isSelf: false })
})

export const buildUserFormFields = ({ isEdit, isSelf }: UserFormFieldOptions) =>
    defineFormFields<UserFormValues>([
        {
            name: 'name',
            label: 'Full name',
            type: 'text',
            placeholder: 'e.g. Ali Raza',
            required: true,
            autocomplete: 'name'
        },
        {
            name: 'email',
            label: 'Login',
            type: 'email',
            placeholder: 'e.g. ali@shop.local',
            description: isEdit
                ? 'Changing the login signs this person out on every device.'
                : 'Used only to log in. No email is ever sent, so any address in this format works.',
            required: true,
            autocomplete: 'off'
        },
        {
            name: 'role',
            label: 'Role',
            type: 'select',
            placeholder: 'Select a role',
            description: isSelf ? 'You cannot change your own role.' : 'Staff can serve customers but cannot manage accounts or shop settings.',
            required: true,
            disabled: isSelf,
            options: userRoleOptions
        },
        {
            name: 'password',
            label: isEdit ? 'New password' : 'Password',
            type: 'password',
            placeholder: isEdit ? 'Leave blank to keep the current password' : 'At least 10 characters with letters and numbers',
            ...(isEdit ? { description: 'Setting a new password signs this person out on every device.' } : {}),
            required: !isEdit,
            autocomplete: 'new-password'
        },
        {
            name: 'password_confirmation',
            label: isEdit ? 'Confirm new password' : 'Confirm password',
            type: 'password',
            placeholder: 'Type the password again',
            required: !isEdit,
            autocomplete: 'new-password'
        }
    ])
