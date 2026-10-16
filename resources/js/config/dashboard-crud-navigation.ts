import type { NavItem } from '@/types'
import type { UserRole } from '@/types/app-data'

export interface DashboardCrudNavigationContract {
    key: string
    item: NavItem
    roles: 'all' | UserRole[]
}

interface DashboardCrudNavigationModule {
    default: DashboardCrudNavigationContract
}

const dashboardCrudNavigationModules = import.meta.glob<DashboardCrudNavigationModule>('../modules/**/contracts/dashboard-nav.ts', {
    eager: true
})

export const filterDashboardCrudNavigationItems = (contracts: DashboardCrudNavigationContract[], role: UserRole | null): NavItem[] => {
    if (role === null) {
        return []
    }

    return contracts.filter((contract) => contract.roles === 'all' || contract.roles.includes(role)).map((contract) => contract.item)
}

export const resolveDashboardCrudNavigationItems = (role: UserRole | null): NavItem[] => {
    const contracts = Object.values(dashboardCrudNavigationModules)
        .map((moduleExport) => moduleExport.default)
        .filter(isValidDashboardCrudNavigationContract)
        .sort((left, right) => left.key.localeCompare(right.key))

    return filterDashboardCrudNavigationItems(contracts, role)
}

const isValidDashboardCrudNavigationContract = (value: DashboardCrudNavigationContract | undefined): value is DashboardCrudNavigationContract => {
    if (value === undefined) {
        return false
    }

    return (
        typeof value.key === 'string' &&
        typeof value.item?.title === 'string' &&
        typeof value.item?.href === 'string' &&
        (value.roles === 'all' || Array.isArray(value.roles))
    )
}
