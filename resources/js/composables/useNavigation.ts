import {
    buildDashboardFooterItems,
    buildDashboardPrimaryItems,
    buildMarketingFooterGroups,
    buildMarketingPrimaryAction,
    buildMarketingPrimaryItems,
    buildMarketingSecondaryAction,
    buildSettingsNavItems,
    type NavigationGroup
} from '@/config/navigation'
import type { NavItem, NavItemActiveMatch } from '@/types'
import { UserRole } from '@/types/app-data'

type ActiveNavItem = NavItem & { isActive: boolean }
type ActiveNavigationGroup = Omit<NavigationGroup, 'items'> & { items: ActiveNavItem[] }

const normalizePath = (value: string): string => {
    const trimmed = value.replace(/\/+$/, '')
    return trimmed === '' ? '/' : trimmed
}

export function useNavigation() {
    const page = useAppPage()
    const user = useAuthUser()
    const isAuthenticated = computed(() => Boolean(user.value?.id))
    const isAdmin = computed(() => user.value?.role === UserRole.Admin)
    // `page.url` is relative, so resolving it needs an origin. `HandleInertiaRequests`
    // shares `location` on every response, which is why this has no fallback.
    const baseLocation = computed(() => page.props.location)
    const currentPath = computed(() => normalizePath(new URL(page.url, baseLocation.value).pathname))

    const context = computed(() => ({
        isAuthenticated: isAuthenticated.value,
        isAdmin: isAdmin.value,
        role: user.value?.role ?? null
    }))

    const isActiveHref = (href: string, activeMatch: NavItemActiveMatch = 'exact'): boolean => {
        const hrefPath = normalizePath(new URL(href, baseLocation.value).pathname)

        if (activeMatch === 'exact' || hrefPath === '/') {
            return currentPath.value === hrefPath
        }

        return currentPath.value === hrefPath || currentPath.value.startsWith(`${hrefPath}/`)
    }

    const withActiveItem = (item: NavItem): ActiveNavItem => ({
        ...item,
        isActive: isActiveHref(item.href, item.activeMatch)
    })

    const withActiveItems = (items: NavItem[]): ActiveNavItem[] => items.map((item) => withActiveItem(item))

    const settingsNavItems = computed<ActiveNavItem[]>(() => withActiveItems(buildSettingsNavItems()))
    const dashboardPrimaryItems = computed<ActiveNavItem[]>(() => withActiveItems(buildDashboardPrimaryItems(context.value)))
    const dashboardFooterItems = computed<ActiveNavItem[]>(() => withActiveItems(buildDashboardFooterItems()))
    const marketingPrimaryItems = computed<ActiveNavItem[]>(() => withActiveItems(buildMarketingPrimaryItems(context.value)))
    const marketingPrimaryAction = computed<ActiveNavItem>(() => withActiveItem(buildMarketingPrimaryAction(context.value)))
    const marketingSecondaryAction = computed<ActiveNavItem>(() => withActiveItem(buildMarketingSecondaryAction(context.value)))
    const marketingFooterGroups = computed<ActiveNavigationGroup[]>(() =>
        buildMarketingFooterGroups(context.value).map((group) => ({
            ...group,
            items: withActiveItems(group.items)
        }))
    )

    return {
        isAuthenticated,
        isAdmin,
        settingsNavItems,
        dashboardPrimaryItems,
        dashboardFooterItems,
        marketingPrimaryItems,
        marketingPrimaryAction,
        marketingSecondaryAction,
        marketingFooterGroups
    }
}
