import type { FormValuesFromData } from '@/lib/forms'
import type { ShopSettingsUpdateData } from '@/types/app-data'

// Inputs edit strings; the server stores an emptied optional field as null.
export type ShopSettingsFormValues = FormValuesFromData<
    ShopSettingsUpdateData,
    {
        phone: string
        address: string
        receipt_footer: string
    }
>

export const createShopSettingsFormValues = (settings: ShopSettingsUpdateData): ShopSettingsFormValues => ({
    name: settings.name,
    phone: settings.phone ?? '',
    address: settings.address ?? '',
    receipt_footer: settings.receipt_footer ?? ''
})

export const shopSettingsFormContract = defineFormContract<ShopSettingsFormValues>({
    defaults: () => createShopSettingsFormValues({ name: '', phone: null, address: null, receipt_footer: null }),
    fields: () =>
        defineFormFields<ShopSettingsFormValues>([
            {
                name: 'name',
                label: 'Shop name',
                type: 'text',
                placeholder: 'e.g. Bismillah Karyana Store',
                description: 'Shown in the app header and printed on receipts and statements.',
                required: true,
                autocomplete: 'organization'
            },
            {
                name: 'phone',
                label: 'Contact phone',
                type: 'tel',
                placeholder: 'e.g. 0300-1234567',
                description: 'Optional. Printed on receipts so customers can reach the shop.',
                autocomplete: 'tel'
            },
            {
                name: 'address',
                label: 'Shop address',
                type: 'textarea',
                placeholder: 'e.g. Shop 4, Main Bazaar, Gulberg, Lahore',
                description: 'Optional. Kept on one line on printed receipts.',
                autocomplete: 'street-address'
            },
            {
                name: 'receipt_footer',
                label: 'Receipt footer',
                type: 'text',
                placeholder: 'e.g. Shukriya! Phir tashreef laiye.',
                description: 'Optional thank-you line printed at the bottom of receipts once receipts are added.',
                autocomplete: 'off'
            }
        ])
})
