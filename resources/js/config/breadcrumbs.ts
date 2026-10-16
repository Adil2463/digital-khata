import type { BreadcrumbItem } from '@/types'
import appRoutes from '../routes/app'

const toSingleBreadcrumb = (title: string, href: string): BreadcrumbItem[] => [
    {
        title,
        href
    }
]

export const buildDashboardBreadcrumbs = (): BreadcrumbItem[] => toSingleBreadcrumb('Dashboard', appRoutes.dashboard.url())

export const buildUsersBreadcrumbs = (): BreadcrumbItem[] => toSingleBreadcrumb('Users', appRoutes.admin.users.index.url())

export const buildSettingsProfileBreadcrumbs = (): BreadcrumbItem[] => toSingleBreadcrumb('Profile settings', appRoutes.settings.profile.edit.url())

export const buildSettingsPasswordBreadcrumbs = (): BreadcrumbItem[] =>
    toSingleBreadcrumb('Password settings', appRoutes.settings.password.edit.url())

export const buildSettingsAppearanceBreadcrumbs = (): BreadcrumbItem[] =>
    toSingleBreadcrumb('Appearance settings', appRoutes.settings.appearance.url())
