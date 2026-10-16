import type { FormValuesFromData } from '@/lib/forms'
import type { LoginData } from '@/types/app-data'

export type LoginFormValues = FormValuesFromData<LoginData>

export const loginFormContract = defineFormContract<LoginFormValues>({
    defaults: () => ({
        email: '',
        password: '',
        remember: false
    }),
    fields: () =>
        defineFormFields<LoginFormValues>([
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
                autocomplete: 'current-password',
                placeholder: 'Password'
            },
            {
                name: 'remember',
                label: 'Remember me',
                type: 'checkbox',
                placeholder: 'Remember me'
            }
        ])
})
