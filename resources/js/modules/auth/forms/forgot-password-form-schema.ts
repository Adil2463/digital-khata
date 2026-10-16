import type { FormValuesFromData } from '@/lib/forms'
import type { PasswordResetLinkData } from '@/types/app-data'

export type ForgotPasswordFormValues = FormValuesFromData<PasswordResetLinkData>

export const forgotPasswordFormContract = defineFormContract<ForgotPasswordFormValues>({
    defaults: () => ({
        email: ''
    }),
    fields: () =>
        defineFormFields<ForgotPasswordFormValues>([
            {
                name: 'email',
                label: 'Email address',
                type: 'email',
                required: true,
                placeholder: 'email@example.com'
            }
        ])
})
