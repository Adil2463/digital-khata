import type { FormValuesFromData } from '@/lib/forms'
import type { ConfirmPasswordData } from '@/types/app-data'

export type ConfirmPasswordFormValues = FormValuesFromData<ConfirmPasswordData>

export const confirmPasswordFormContract = defineFormContract<ConfirmPasswordFormValues>({
    defaults: () => ({
        password: ''
    }),
    fields: () =>
        defineFormFields<ConfirmPasswordFormValues>([
            {
                name: 'password',
                label: 'Password',
                type: 'password',
                required: true,
                autocomplete: 'current-password',
                placeholder: 'Password'
            }
        ])
})
