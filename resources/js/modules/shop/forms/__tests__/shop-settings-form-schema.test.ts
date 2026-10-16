import { describe, expect, it } from 'vitest'
import { createShopSettingsFormValues, shopSettingsFormContract } from '../shop-settings-form-schema'

describe('shop settings form', () => {
    it('edits only the owner-editable fields, never the shop id or timezone', () => {
        const fields = shopSettingsFormContract.fields()

        expect(fields.map((field) => field.name)).toEqual(['name', 'phone', 'address', 'receipt_footer'])
        expect(fields.filter((field) => field.required === true).map((field) => field.name)).toEqual(['name'])
        expect(fields.find((field) => field.name === 'phone')?.type).toBe('tel')
    })

    it('turns empty optional values into editable strings', () => {
        expect(createShopSettingsFormValues({ name: 'Bismillah Store', phone: null, address: null, receipt_footer: 'Shukriya!' })).toEqual({
            name: 'Bismillah Store',
            phone: '',
            address: '',
            receipt_footer: 'Shukriya!'
        })
    })
})
