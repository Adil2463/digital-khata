export enum Appearance {
    Light = 'light',
    Dark = 'dark'
}
export type ConfirmPasswordData = {
    password: string
}
export type CreateUserData = {
    name: string
    email: string
    role: UserRole
    password: string
}
export type CursorPaginatedDataCollection<TKey, TValue> = CursorPaginator<TKey, TValue>
export type CursorPaginator<TKey, TValue> = {
    data: TKey extends string ? Record<TKey, TValue> : TValue[]
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    meta: {
        path: string
        per_page: number
        next_cursor: string | null
        next_page_url: string | null
        prev_cursor: string | null
        prev_page_url: string | null
    }
}
export type CursorPaginatorInterface<TKey, TValue> = CursorPaginator<TKey, TValue>
export type ForgotPasswordPageData = {
    status: string | null
}
export type LengthAwarePaginator<TKey, TValue> = {
    data: TKey extends string ? Record<TKey, TValue> : TValue[]
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    meta: {
        total: number
        current_page: number
        first_page_url: string
        from: number | null
        last_page: number
        last_page_url: string
        next_page_url: string | null
        path: string
        per_page: number
        prev_page_url: string | null
        to: number | null
    }
}
export type LengthAwarePaginatorInterface<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type LoginData = {
    email: string
    password: string
    remember: boolean
}
export type LoginPageData = {
    canResetPassword: boolean
    status: string | null
}
export type PaginatedDataCollection<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type PaginationData = {
    current_page: number
    last_page: number
    per_page: number
    total: number
}
export type PaginationQueryData = {
    page: number
    perPage: number
}
export type PasswordResetLinkData = {
    email: string
}
export type PasswordUpdateData = {
    currentPassword: string
    password: string
    passwordConfirmation: string
}
export type PresenceMemberData = {
    id: number
    name: string
    role: UserRole
}
export type ProfileDestroyData = {
    password: string
}
export type ProfilePageData = {
    mustVerifyEmail: boolean
    status: string | null
}
export type ProfileUpdateData = {
    name: string
    email: string
}
export type RegisterUserData = {
    name: string
    email: string
    password: string
}
export type ResetPasswordData = {
    token: string
    email: string
    password: string
    passwordConfirmation: string
}
export type ResetPasswordPageData = {
    email: string
    token: string
}
export type SharedAuthData = {
    user: UserViewData | null
}
export type SharedFlashData = {
    message: string | null
    error: string | null
    status: string | null
}
export type SharedPageData = {
    name: string
    quote: SharedQuoteData
    auth: SharedAuthData
    flash: SharedFlashData
    location: string
    sidebarOpen: boolean
    appearance: Appearance
}
export type SharedQuoteData = {
    message: string
    author: string
}
export enum SharedRealtimeChannel {
    UserNotifications = 'users.{userId}.notifications'
}
export enum SortDirection {
    Asc = 'asc',
    Desc = 'desc'
}
export type UpdateUserData = {
    name: string
    email: string
    role: UserRole
    password: string | null
}
export type UserChangedBroadcastData = {
    action: UsersRealtimeAction
    actorUserId: number
    targetUserId: number
    user: UserViewData | null
    occurredAt: string
}
export type UserIndexData = {
    page: number
    perPage: number
    search: string | null
    sortBy: UserSortBy
    sortDirection: SortDirection
}
export type UserManagementNotificationData = {
    title: string
    description: string
    action: UsersRealtimeAction
    actorUserId: number
    actorName: string
    targetUserId: number | null
    occurredAt: string
}
export enum UserRole {
    Admin = 'admin',
    User = 'user'
}
export enum UserSortBy {
    Name = 'name',
    Email = 'email',
    Role = 'role',
    CreatedAt = 'created_at'
}
export type UserViewData = {
    id: number
    name: string
    email: string
    role: UserRole
    created_at: string
    updated_at: string
    email_verified_at: string | null
}
export type UsersIndexPageData = {
    items: UserViewData[]
    pagination: PaginationData
}
export type UsersListChangedBroadcastData = {
    action: UsersRealtimeAction
    actorUserId: number
    targetUserId: number | null
    occurredAt: string
}
export enum UsersRealtimeAction {
    Create = 'create',
    Update = 'update',
    Delete = 'delete'
}
export enum UsersRealtimeChannel {
    Index = 'users.index',
    Presence = 'users.index.presence',
    User = 'users.{userId}'
}
export enum UsersRealtimeEvent {
    ListChanged = 'users.list.changed',
    UserChanged = 'users.user.changed'
}
export type VerifyEmailPageData = {
    status: string | null
}
