import type { FormValuesFromData } from '@/lib/forms'
import type { CreateUserData } from '@/types/app-data'
import { UserRole } from '@/types/app-data'

export type UserFormValues = FormValuesFromData<
    CreateUserData,
    {
        role: CreateUserData['role'] | ''
    }
>

const roleOptions = getEnumOptions(UserRole).map((role) => ({
    value: role.value,
    label: capitalize(role.label)
}))

export const createUserFormDefaults = (): UserFormValues => ({
    name: '',
    email: '',
    role: '',
    password: ''
})

export const userFormContract = defineFormContract<UserFormValues>({
    defaults: createUserFormDefaults,
    fields: () => buildUserFormFields(false)
})

export const buildUserFormFields = (isEdit: boolean) =>
    defineFormFields<UserFormValues>([
        {
            name: 'name',
            label: 'Name',
            type: 'text',
            placeholder: 'Enter name',
            required: true,
            autocomplete: 'name'
        },
        {
            name: 'email',
            label: 'Email',
            type: 'email',
            placeholder: 'Enter email',
            required: true,
            autocomplete: 'email'
        },
        {
            name: 'role',
            label: 'Role',
            type: 'select',
            placeholder: 'Select a role',
            required: true,
            options: roleOptions
        },
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            placeholder: isEdit ? 'Leave empty to keep current password' : 'Enter password',
            required: !isEdit,
            autocomplete: isEdit ? 'new-password' : 'current-password'
        }
    ])
