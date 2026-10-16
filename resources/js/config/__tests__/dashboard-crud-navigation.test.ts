import { UserRole } from '@/types/app-data'
import { describe, expect, it } from 'vitest'
import { filterDashboardCrudNavigationItems, type DashboardCrudNavigationContract } from '../dashboard-crud-navigation'

const contracts: DashboardCrudNavigationContract[] = [
    {
        key: 'billing',
        item: {
            title: 'Billing',
            href: '/app/billing',
            activeMatch: 'prefix'
        },
        roles: [UserRole.Admin]
    },
    {
        key: 'knowledge-base',
        item: {
            title: 'Knowledge Base',
            href: '/app/knowledge-base',
            activeMatch: 'prefix'
        },
        roles: [UserRole.User]
    },
    {
        key: 'support',
        item: {
            title: 'Support',
            href: '/app/support',
            activeMatch: 'prefix'
        },
        roles: 'all'
    }
]

describe('dashboard-crud-navigation', () => {
    it('returns no dashboard module items when role is missing', () => {
        expect(filterDashboardCrudNavigationItems(contracts, null)).toEqual([])
    })

    it('returns only admin and all-role module items for admin users', () => {
        const items = filterDashboardCrudNavigationItems(contracts, UserRole.Admin)

        expect(items.map((item) => item.title)).toEqual(['Billing', 'Support'])
    })

    it('returns only user and all-role module items for user role', () => {
        const items = filterDashboardCrudNavigationItems(contracts, UserRole.User)

        expect(items.map((item) => item.title)).toEqual(['Knowledge Base', 'Support'])
    })
})
