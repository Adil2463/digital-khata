import { UserRole } from '@/types/app-data'
import { describe, expect, it } from 'vitest'
import { isAccountDeactivated, userRoleLabel } from '../account-labels'

describe('account labels', () => {
    it('labels backend roles for the shop', () => {
        expect(userRoleLabel(UserRole.Admin)).toBe('Owner')
        expect(userRoleLabel(UserRole.User)).toBe('Staff')
    })

    it('treats a deactivation timestamp as deactivated', () => {
        expect(isAccountDeactivated({ deactivated_at: null })).toBe(false)
        expect(isAccountDeactivated({ deactivated_at: '2026-10-17T09:00:00+00:00' })).toBe(true)
    })
})
