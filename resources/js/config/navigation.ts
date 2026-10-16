import { resolveDashboardCrudNavigationItems } from '@/config/dashboard-crud-navigation'
import type { NavItem } from '@/types'
import type { UserRole } from '@/types/app-data'
import IconLucideLayoutGrid from '~icons/lucide/layout-grid'
import IconLucideLockKeyhole from '~icons/lucide/lock-keyhole'
import IconLucideLogIn from '~icons/lucide/log-in'
import IconLucideSettings from '~icons/lucide/settings'
import IconLucideUserPlus from '~icons/lucide/user-plus'
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
    settingsProfile: () => appRoutes.settings.profile.edit.url(),
    settingsPassword: () => appRoutes.settings.password.edit.url(),
    settingsAppearance: () => appRoutes.settings.appearance.url(),
    authRegister: () => authRoutes.register.create.url(),
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

export const buildDashboardPrimaryItems = (context: NavigationContext): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix',
            icon: IconLucideLayoutGrid
        }
    ]

    if (context.isAdmin) {
        items.push({
            title: 'Users',
            href: navRoutes.adminUsers(),
            activeMatch: 'prefix',
            icon: IconLucideUsers
        })
    }

    items.push(...resolveDashboardCrudNavigationItems(context.role))

    return items
}

export const buildDashboardFooterItems = (): NavItem[] => [
    {
        title: 'Settings',
        href: navRoutes.settingsProfile(),
        icon: IconLucideSettings
    },
    {
        title: 'Security',
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
            items.push({
                title: 'Users',
                href: navRoutes.adminUsers(),
                activeMatch: 'prefix'
            })
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
        title: 'Get started',
        href: navRoutes.authRegister(),
        icon: IconLucideUserPlus
    }
}

export const buildMarketingSecondaryAction = (context: NavigationContext): NavItem => {
    if (context.isAuthenticated) {
        return {
            title: 'Settings',
            href: navRoutes.settingsProfile(),
            icon: IconLucideSettings
        }
    }

    return {
        title: 'Log in',
        href: navRoutes.authLogin(),
        icon: IconLucideLogIn
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
              },
              {
                  title: 'Create account',
                  href: navRoutes.authRegister()
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
            title: 'Administration',
            items: [
                {
                    title: 'User management',
                    href: navRoutes.adminUsers(),
                    activeMatch: 'prefix'
                }
            ]
        })
    }

    return groups
}
