import type { FormValuesFromData } from '@/lib/forms'
import type { ResetPasswordData } from '@/types/app-data'

export type ResetPasswordFormValues = FormValuesFromData<
    ResetPasswordData,
    {
        password_confirmation: string
    },
    'passwordConfirmation'
>

export const createResetPasswordFormDefaults = (token: string, email: string): ResetPasswordFormValues => ({
    token,
    email,
    password: '',
    password_confirmation: ''
})

export const resetPasswordFormContract = defineFormContract<ResetPasswordFormValues>({
    defaults: () => createResetPasswordFormDefaults('', ''),
    fields: () =>
        defineFormFields<ResetPasswordFormValues>([
            {
                name: 'email',
                label: 'Email',
                type: 'email',
                readonly: true
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
                label: 'Confirm Password',
                type: 'password',
                required: true,
                autocomplete: 'new-password',
                placeholder: 'Confirm password'
            }
        ])
})
