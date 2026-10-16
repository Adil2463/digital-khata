import type { FormValuesFromData } from '@/lib/forms'
import type { PasswordUpdateData } from '@/types/app-data'

export type PasswordFormValues = FormValuesFromData<
    PasswordUpdateData,
    {
        current_password: string
        password_confirmation: string
    },
    'currentPassword' | 'passwordConfirmation'
>

export const passwordFormContract = defineFormContract<PasswordFormValues>({
    defaults: () => ({
        current_password: '',
        password: '',
        password_confirmation: ''
    }),
    fields: () =>
        defineFormFields<PasswordFormValues>([
            {
                name: 'current_password',
                label: 'Current password',
                type: 'password',
                autocomplete: 'current-password',
                placeholder: 'Current password'
            },
            {
                name: 'password',
                label: 'New password',
                type: 'password',
                autocomplete: 'new-password',
                placeholder: 'New password'
            },
            {
                name: 'password_confirmation',
                label: 'Confirm password',
                type: 'password',
                autocomplete: 'new-password',
                placeholder: 'Confirm password'
            }
        ])
})
