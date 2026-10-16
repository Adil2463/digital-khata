import type { UserViewData } from '@/types/app-data'
import { UserRole } from '@/types/app-data'

const userRoleLabels: Record<UserRole, string> = {
    [UserRole.Admin]: 'Owner',
    [UserRole.User]: 'Staff'
}

export const userRoleLabel = (role: UserRole): string => userRoleLabels[role]

export const userRoleOptions = Object.values(UserRole).map((role) => ({
    value: role,
    label: userRoleLabel(role)
}))

export const isAccountDeactivated = (user: Pick<UserViewData, 'deactivated_at'>): boolean => user.deactivated_at !== null
