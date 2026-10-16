<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { SortDirection, UserSortBy, type UsersIndexPageData, type UserViewData } from '@/types/app-data'
    import IconLucidePlus from '~icons/lucide/plus'
    import { useUsersIndexRealtime } from '../contracts/realtime'
    import type { UserSortColumn } from '../contracts/types'

    const theme = appTheme

    const userSortColumns = [UserSortBy.Name, UserSortBy.Email, UserSortBy.Role, UserSortBy.CreatedAt] as const

    const page = useAppPage()
    const authenticatedUser = useAuthUser({ required: true, context: 'UsersIndexPage' })
    const props = defineProps<UsersIndexPageData>()
    const currentUserId = computed(() => authenticatedUser.value.id)

    const breadcrumbs = buildUsersBreadcrumbs()

    const isUserDialogOpen = ref(false)
    const isDeleteDialogOpen = ref(false)
    const selectedUser = ref<UserViewData | null>(null)
    const userDialogMode = ref<'create' | 'edit'>('create')

    const locationSearch = computed(() => {
        try {
            return new URL(page.props.location).search
        } catch {
            return ''
        }
    })

    const initialQuery = computed(() =>
        resolveServerTableInitialQuery<UserSortColumn>({
            locationSearch: locationSearch.value,
            fallback: {
                page: props.pagination.current_page,
                perPage: props.pagination.per_page,
                sortBy: UserSortBy.CreatedAt,
                sortDirection: SortDirection.Desc
            },
            allowedSortBy: userSortColumns,
            defaultSortBy: UserSortBy.CreatedAt,
            defaultSortDirection: SortDirection.Desc
        })
    )

    const { query, searchValue, setPage, setPerPage, setSort } = useServerDataTable<UserSortColumn>({
        endpoint: UserController.index,
        initialQuery,
        debounceMs: 300
    })

    const { activeCollaboratorCount } = useUsersIndexRealtime({
        currentUserId,
        onListChanged: () => {
            if (!isUserDialogOpen.value && !isDeleteDialogOpen.value) {
                router.reload({
                    only: ['items', 'pagination']
                })
            }
        }
    })

    const usersTableProps = computed(() => {
        const resolved: {
            users: UserViewData[]
            currentUserId: number
            sortBy?: UserSortColumn
            sortDirection?: 'asc' | 'desc'
        } = {
            users: props.items,
            currentUserId: currentUserId.value
        }

        if (query.value.sortBy !== undefined) {
            resolved.sortBy = query.value.sortBy
        }

        if (query.value.sortDirection !== undefined) {
            resolved.sortDirection = query.value.sortDirection
        }

        return resolved
    })

    const openCreateUserDialog = (): void => {
        userDialogMode.value = 'create'
        selectedUser.value = null
        isUserDialogOpen.value = true
    }

    const onEditUser = (user: UserViewData): void => {
        selectedUser.value = user
        userDialogMode.value = 'edit'
        isUserDialogOpen.value = true
    }

    const onUserSaved = (): void => {
        isUserDialogOpen.value = false
    }

    const onDeleteUser = (user: UserViewData): void => {
        selectedUser.value = user
        isDeleteDialogOpen.value = true
    }

    const onUserDeleted = (): void => {
        isDeleteDialogOpen.value = false
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users" />
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 rounded-xl p-3 sm:p-4">
            <div class="mt-2 flex flex-col gap-4 sm:mt-4">
                <div :class="theme.layout.pageHeader">
                    <Heading
                        title="Users"
                        :description="`Manage user accounts${activeCollaboratorCount > 0 ? ` - ${activeCollaboratorCount} admin${activeCollaboratorCount === 1 ? '' : 's'} online` : ''}`"
                    />
                    <BaseButton class="w-full sm:w-auto" label="Add User" :icon-left="IconLucidePlus" @click="openCreateUserDialog" />
                </div>

                <BaseTableBaseDataTableToolbar
                    :search-value="searchValue"
                    :per-page="query.perPage"
                    search-placeholder="Search users..."
                    @update:search-value="searchValue = $event"
                    @update:per-page="setPerPage($event)"
                />

                <UsersTable v-bind="usersTableProps" @edit="onEditUser" @delete="onDeleteUser" @sort="setSort($event)" />

                <BaseTableBaseDataTablePagination
                    :current-page="props.pagination.current_page"
                    :total-pages="props.pagination.last_page"
                    :items-per-page="props.pagination.per_page"
                    :total-items="props.pagination.total"
                    @page-change="setPage($event)"
                />
            </div>

            <UsersUserFormDialog
                v-if="isUserDialogOpen"
                :open="isUserDialogOpen"
                :mode="userDialogMode"
                :user="selectedUser"
                @update:open="isUserDialogOpen = $event"
                @created="onUserSaved"
                @updated="onUserSaved"
            />

            <UsersDeleteUserDialog
                v-if="isDeleteDialogOpen && selectedUser"
                :open="isDeleteDialogOpen"
                :user="selectedUser"
                @update:open="isDeleteDialogOpen = $event"
                @deleted="onUserDeleted"
            />
        </div>
    </AppLayout>
</template>
