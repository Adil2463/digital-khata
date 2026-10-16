import type { AppPageProps } from '@/types'
import { Appearance, UserRole } from '@/types/app-data'
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
        expect(footerByTitle.get('Security')?.isActive).toBe(true)
    })

    it('marks parent admin users link active for nested users paths', () => {
        mockPage = createMockPage({
            url: '/app/admin/users/42/edit?tab=details',
            role: UserRole.Admin
        })
        usePageMock.mockReturnValue(mockPage)

        const { dashboardPrimaryItems } = useNavigation()
        const itemsByTitle = new Map(dashboardPrimaryItems.value.map((item) => [item.title, item]))

        expect(itemsByTitle.get('Dashboard')?.isActive).toBe(false)
        expect(itemsByTitle.get('Users')?.isActive).toBe(true)
    })

    it('keeps home inactive off root and hides admin links for non-admin users', () => {
        mockPage = createMockPage({
            url: '/app/dashboard',
            role: UserRole.User
        })
        usePageMock.mockReturnValue(mockPage)

        const { dashboardPrimaryItems, marketingPrimaryItems } = useNavigation()
        const marketingByTitle = new Map(marketingPrimaryItems.value.map((item) => [item.title, item]))

        expect(marketingByTitle.get('Home')?.isActive).toBe(false)
        expect(marketingByTitle.get('Dashboard')?.isActive).toBe(true)
        expect(dashboardPrimaryItems.value.some((item) => item.title === 'Users')).toBe(false)
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
        email_verified_at: null
    }
}
