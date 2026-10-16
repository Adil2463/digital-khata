import type { FormValuesFromData } from '@/lib/forms'
import type { ProfileUpdateData } from '@/types/app-data'

export type ProfileFormValues = FormValuesFromData<ProfileUpdateData>

export const createProfileFormDefaults = (user: Pick<ProfileFormValues, 'name' | 'email'>): ProfileFormValues => ({
    name: user.name,
    email: user.email
})

export const profileFormContract = defineFormContract<ProfileFormValues>({
    defaults: () =>
        createProfileFormDefaults({
            name: '',
            email: ''
        }),
    fields: () =>
        defineFormFields<ProfileFormValues>([
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
                autocomplete: 'username',
                placeholder: 'Email address'
            }
        ])
})
