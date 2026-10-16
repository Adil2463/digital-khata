import { resolveDashboardCrudNavigationItems } from '@/config/dashboard-crud-navigation'
import type { NavItem } from '@/types'
import type { UserRole } from '@/types/app-data'
import IconLucideLayoutGrid from '~icons/lucide/layout-grid'
import IconLucideLockKeyhole from '~icons/lucide/lock-keyhole'
import IconLucideLogIn from '~icons/lucide/log-in'
import IconLucideSettings from '~icons/lucide/settings'
import IconLucideStore from '~icons/lucide/store'
import IconLucideUsers from '~icons/lucide/users'
import appRoutes from '../routes/app'
import authRoutes from '../routes/auth'
import marketingRoutes from '../routes/marketing'

export interface NavigationContext {
    isAuthenticated: boolean
    isAdmin: boolean
    role: UserRole | null
}

export interface NavigationGroup {
    title: string
    items: NavItem[]
}

const navRoutes = {
    marketingHome: () => marketingRoutes.home.url(),
    appDashboard: () => appRoutes.dashboard.url(),
    adminUsers: () => appRoutes.admin.users.index.url(),
    shopSettings: () => appRoutes.shop.settings.edit.url(),
    settingsProfile: () => appRoutes.settings.profile.edit.url(),
    settingsPassword: () => appRoutes.settings.password.edit.url(),
    settingsAppearance: () => appRoutes.settings.appearance.url(),
    authLogin: () => authRoutes.login.create.url()
}

export const buildSettingsNavItems = (): NavItem[] => [
    {
        title: 'Profile',
        href: navRoutes.settingsProfile()
    },
    {
        title: 'Password',
        href: navRoutes.settingsPassword()
    },
    {
        title: 'Appearance',
        href: navRoutes.settingsAppearance()
    }
]

// Owner-only screens. Hiding them is a convenience: each route is also guarded by its gate.
const buildOwnerItems = (): NavItem[] => [
    {
        title: 'Staff',
        href: navRoutes.adminUsers(),
        activeMatch: 'prefix',
        icon: IconLucideUsers
    },
    {
        title: 'Shop settings',
        href: navRoutes.shopSettings(),
        activeMatch: 'prefix',
        icon: IconLucideStore
    }
]

export const buildDashboardNavGroups = (context: NavigationContext): NavigationGroup[] => {
    const groups: NavigationGroup[] = [
        {
            title: 'Menu',
            items: [
                {
                    title: 'Dashboard',
                    href: navRoutes.appDashboard(),
                    activeMatch: 'prefix',
                    icon: IconLucideLayoutGrid
                },
                ...resolveDashboardCrudNavigationItems(context.role)
            ]
        }
    ]

    if (context.isAdmin) {
        groups.push({
            title: 'Manage shop',
            items: buildOwnerItems()
        })
    }

    return groups
}

export const buildDashboardFooterItems = (): NavItem[] => [
    {
        title: 'My account',
        href: navRoutes.settingsProfile(),
        icon: IconLucideSettings
    },
    {
        title: 'Password',
        href: navRoutes.settingsPassword(),
        icon: IconLucideLockKeyhole
    }
]

export const buildMarketingPrimaryItems = (context: NavigationContext): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Home',
            href: navRoutes.marketingHome()
        }
    ]

    if (context.isAuthenticated) {
        items.push({
            title: 'Dashboard',
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix'
        })

        if (context.isAdmin) {
            items.push(...buildOwnerItems())
        }
    }

    return items
}

export const buildMarketingPrimaryAction = (context: NavigationContext): NavItem => {
    if (context.isAuthenticated) {
        return {
            title: 'Open dashboard',
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix',
            icon: IconLucideLayoutGrid
        }
    }

    return {
        title: 'Log in',
        href: navRoutes.authLogin(),
        icon: IconLucideLogIn
    }
}

export const buildMarketingSecondaryAction = (context: NavigationContext): NavItem | null => {
    if (!context.isAuthenticated) {
        return null
    }

    return {
        title: 'My account',
        href: navRoutes.settingsProfile(),
        icon: IconLucideSettings
    }
}

export const buildMarketingFooterGroups = (context: NavigationContext): NavigationGroup[] => {
    const platformItems: NavItem[] = [
        {
            title: 'Home',
            href: navRoutes.marketingHome()
        }
    ]

    if (context.isAuthenticated) {
        platformItems.push({
            title: 'Dashboard',
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix'
        })
    }

    const accountItems: NavItem[] = context.isAuthenticated
        ? [
              {
                  title: 'Profile settings',
                  href: navRoutes.settingsProfile()
              },
              {
                  title: 'Password settings',
                  href: navRoutes.settingsPassword()
              }
          ]
        : [
              {
                  title: 'Log in',
                  href: navRoutes.authLogin()
              }
          ]

    const groups: NavigationGroup[] = [
        {
            title: 'Platform',
            items: platformItems
        },
        {
            title: 'Account',
            items: accountItems
        }
    ]

    if (context.isAdmin) {
        groups.push({
            title: 'Manage shop',
            items: buildOwnerItems()
        })
    }

    return groups
}
