import type { FormValuesFromData } from '@/lib/forms'
import type { RegisterUserData } from '@/types/app-data'

export type RegisterFormValues = FormValuesFromData<
    RegisterUserData,
    {
        password_confirmation: string
    }
>

export const registerFormContract = defineFormContract<RegisterFormValues>({
    defaults: () => ({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    }),
    fields: () =>
        defineFormFields<RegisterFormValues>([
            {
                name: 'name',
                label: 'Name',
                type: 'text',
                required: true,
                autocomplete: 'name',
                placeholder: 'Full name'
            },
            {
                name: 'email',
                label: 'Email address',
                type: 'email',
                required: true,
                autocomplete: 'email',
                placeholder: 'email@example.com'
            },
            {
                name: 'password',
                label: 'Password',
                type: 'password',
                required: true,
                autocomplete: 'new-password',
                placeholder: 'Password'
            },
            {
                name: 'password_confirmation',
                label: 'Confirm password',
                type: 'password',
                required: true,
                autocomplete: 'new-password',
                placeholder: 'Confirm password'
            }
        ])
})
