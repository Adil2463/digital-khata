import type { AppPageProps } from '@/types'
import { Appearance, ShopTimezone, UserRole } from '@/types/app-data'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { useNavigation } from '../useNavigation'

type MockPage = {
    url: string
    props: AppPageProps
}

let mockPage = createMockPage({ url: '/', role: null })

const { usePageMock } = vi.hoisted(() => ({
    usePageMock: vi.fn()
}))

vi.mock('@inertiajs/vue3', () => ({
    usePage: usePageMock
}))

beforeEach(() => {
    usePageMock.mockClear()
    usePageMock.mockReturnValue(mockPage)
})

describe('useNavigation', () => {
    it('marks settings links active using centralized item state', () => {
        mockPage = createMockPage({
            url: '/app/settings/password?source=sidebar',
            role: UserRole.User
        })
        usePageMock.mockReturnValue(mockPage)

        const { settingsNavItems, dashboardFooterItems } = useNavigation()
        const settingsByTitle = new Map(settingsNavItems.value.map((item) => [item.title, item]))
        const footerByTitle = new Map(dashboardFooterItems.value.map((item) => [item.title, item]))

        expect(settingsByTitle.get('Profile')?.isActive).toBe(false)
        expect(settingsByTitle.get('Password')?.isActive).toBe(true)
        expect(settingsByTitle.get('Appearance')?.isActive).toBe(false)
        expect(footerByTitle.get('My account')?.isActive).toBe(false)
        expect(footerByTitle.get('Password')?.isActive).toBe(true)
    })

    it('marks parent admin users link active for nested users paths', () => {
        mockPage = createMockPage({
            url: '/app/admin/users/42/edit?tab=details',
            role: UserRole.Admin
        })
        usePageMock.mockReturnValue(mockPage)

        const { dashboardNavGroups } = useNavigation()
        const itemsByTitle = new Map(dashboardNavGroups.value.flatMap((group) => group.items).map((item) => [item.title, item]))

        expect(itemsByTitle.get('Dashboard')?.isActive).toBe(false)
        expect(itemsByTitle.get('Staff')?.isActive).toBe(true)
    })

    it('keeps home inactive off root and hides admin links for non-admin users', () => {
        mockPage = createMockPage({
            url: '/app/dashboard',
            role: UserRole.User
        })
        usePageMock.mockReturnValue(mockPage)

        const { dashboardNavGroups, marketingPrimaryItems } = useNavigation()
        const marketingByTitle = new Map(marketingPrimaryItems.value.map((item) => [item.title, item]))

        expect(marketingByTitle.get('Home')?.isActive).toBe(false)
        expect(marketingByTitle.get('Dashboard')?.isActive).toBe(true)
        expect(dashboardNavGroups.value.map((group) => group.title)).toEqual(['Menu'])
        const staffTitles = dashboardNavGroups.value.flatMap((group) => group.items).map((item) => item.title)

        // Generated CRUD modules may add their own items to the same group.
        expect(staffTitles[0]).toBe('Dashboard')
        expect(staffTitles).not.toContain('Staff')
        expect(staffTitles).not.toContain('Shop settings')
        expect(marketingPrimaryItems.value.map((item) => item.title)).toEqual(['Home', 'Dashboard'])
    })

    it('offers guests only the login action because public registration is disabled', () => {
        mockPage = createMockPage({ url: '/', role: null })
        usePageMock.mockReturnValue(mockPage)

        const { marketingPrimaryAction, marketingSecondaryAction, marketingFooterGroups } = useNavigation()
        const footerTitles = marketingFooterGroups.value.flatMap((group) => group.items.map((item) => item.title))

        expect(marketingPrimaryAction.value).toMatchObject({ title: 'Log in', href: '/auth/login' })
        expect(marketingSecondaryAction.value).toBeNull()
        expect(footerTitles).toContain('Log in')
        expect(footerTitles).not.toContain('Create account')
        expect(JSON.stringify(marketingFooterGroups.value)).not.toContain('/auth/register')
    })

    it('keeps the settings shortcut for authenticated visitors', () => {
        mockPage = createMockPage({ url: '/', role: UserRole.User })
        usePageMock.mockReturnValue(mockPage)

        const { marketingPrimaryAction, marketingSecondaryAction } = useNavigation()

        expect(marketingPrimaryAction.value.title).toBe('Open dashboard')
        expect(marketingSecondaryAction.value?.title).toBe('My account')
    })

    it('gives owners a manage-shop group with staff and shop settings', () => {
        mockPage = createMockPage({ url: '/app/shop/settings', role: UserRole.Admin })
        usePageMock.mockReturnValue(mockPage)

        const { dashboardNavGroups, marketingFooterGroups } = useNavigation()
        const ownerGroup = dashboardNavGroups.value.find((group) => group.title === 'Manage shop')

        expect(dashboardNavGroups.value.map((group) => group.title)).toEqual(['Menu', 'Manage shop'])
        expect(ownerGroup?.items.map((item) => [item.title, item.href, item.isActive])).toEqual([
            ['Staff', '/app/admin/users', false],
            ['Shop settings', '/app/shop/settings', true]
        ])
        expect(marketingFooterGroups.value.find((group) => group.title === 'Manage shop')?.items.map((item) => item.title)).toEqual([
            'Staff',
            'Shop settings'
        ])
    })

    it('never shows owner screens to staff or guests', () => {
        for (const role of [UserRole.User, null]) {
            mockPage = createMockPage({ url: '/app/dashboard', role })
            usePageMock.mockReturnValue(mockPage)

            const { dashboardNavGroups, marketingPrimaryItems, marketingFooterGroups } = useNavigation()
            const everyHref = JSON.stringify([dashboardNavGroups.value, marketingPrimaryItems.value, marketingFooterGroups.value])

            expect(everyHref).not.toContain('/app/admin/users')
            expect(everyHref).not.toContain('/app/shop/settings')
        }
    })
})

function createMockPage({ url, role }: { url: string; role: UserRole | null }): MockPage {
    return {
        url,
        props: {
            name: 'Test Page',
            quote: {
                message: '',
                author: ''
            },
            auth: {
                user: role === null ? null : createUser(role)
            },
            shop: role === null ? null : { name: 'Bismillah Karyana Store', timezone: ShopTimezone.AsiaKarachi },
            flash: { message: null, error: null, status: null },
            location: `http://localhost${url}`,
            sidebarOpen: true,
            appearance: Appearance.Light
        }
    }
}

function createUser(role: UserRole): NonNullable<AppPageProps['auth']['user']> {
    return {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        role,
        created_at: '2026-01-01T00:00:00+00:00',
        updated_at: '2026-01-01T00:00:00+00:00',
        email_verified_at: null,
        deactivated_at: null
    }
}
