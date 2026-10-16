<script setup lang="ts">
    import { UserRole, type UserViewData } from '@/types/app-data'
    import type { DataTableColumn, DataTableRowAction, MobileCardField } from '@/types/base-ui'
    import type { UserSortColumn } from '../contracts/types'

    interface Props {
        currentUserId: number
        users: UserViewData[]
        sortBy?: UserSortColumn
        sortDirection?: 'asc' | 'desc'
    }

    const theme = appTheme

    const props = defineProps<Props>()

    const emit = defineEmits<{
        edit: [user: UserViewData]
        changeStatus: [user: UserViewData]
        sort: [column: UserSortColumn]
    }>()

    const statusLabel = (user: UserViewData): string => (isAccountDeactivated(user) ? 'Deactivated' : 'Active')
    const statusClass = (user: UserViewData): string[] => [
        theme.feedback.pill,
        isAccountDeactivated(user) ? theme.feedback.pillTone.muted : theme.feedback.pillTone.success
    ]
    const roleClass = (user: UserViewData): string[] => [
        theme.feedback.pill,
        user.role === UserRole.Admin ? theme.feedback.pillTone.info : theme.feedback.pillTone.muted
    ]
    const roleLabel = (user: UserViewData): string => userRoleLabel(user.role)
    const isCurrentUser = (user: UserViewData): boolean => user.id === props.currentUserId

    const columns: Array<DataTableColumn<UserViewData, UserSortColumn>> = [
        {
            key: 'name',
            label: 'Name',
            sortable: true,
            sortKey: 'name',
            value: (row) => row.name
        },
        {
            key: 'role',
            label: 'Role',
            sortable: true,
            sortKey: 'role',
            value: (row) => userRoleLabel(row.role)
        },
        {
            key: 'status',
            label: 'Status',
            value: (row) => statusLabel(row)
        },
        {
            key: 'created_at',
            label: 'Added',
            sortable: true,
            sortKey: 'created_at',
            value: (row) => formatDate(row.created_at)
        }
    ]

    const mobileFields: Array<MobileCardField<UserViewData>> = [
        {
            key: 'role',
            label: 'Role',
            value: (row) => userRoleLabel(row.role)
        },
        {
            key: 'status',
            label: 'Status',
            value: (row) => statusLabel(row)
        },
        {
            key: 'created_at',
            label: 'Added',
            value: (row) => formatDate(row.created_at)
        }
    ]

    const actions = computed<Array<DataTableRowAction<UserViewData>>>(() => [
        {
            key: 'edit',
            label: 'Edit',
            onClick: (row) => emit('edit', row)
        },
        {
            key: 'deactivate',
            label: 'Deactivate',
            destructive: true,
            visible: (row) => !isCurrentUser(row) && !isAccountDeactivated(row),
            onClick: (row) => emit('changeStatus', row)
        },
        {
            key: 'reactivate',
            label: 'Reactivate',
            visible: (row) => isAccountDeactivated(row),
            onClick: (row) => emit('changeStatus', row)
        }
    ])

    const resolveSlotRow = (slotProps: { row: UserViewData }): UserViewData => slotProps.row

    const dataTableProps = computed(() => {
        const tableProps: {
            rows: UserViewData[]
            columns: Array<DataTableColumn<UserViewData, UserSortColumn>>
            rowKey: (row: UserViewData) => number
            actions: Array<DataTableRowAction<UserViewData>>
            emptyMessage: string
            sortBy?: UserSortColumn
            sortDirection?: 'asc' | 'desc'
        } = {
            rows: props.users,
            columns,
            rowKey: (row) => row.id,
            actions: actions.value,
            emptyMessage: 'No accounts found.'
        }

        if (props.sortBy !== undefined) {
            tableProps.sortBy = props.sortBy
        }

        if (props.sortDirection !== undefined) {
            tableProps.sortDirection = props.sortDirection
        }

        return tableProps
    })
</script>

<template>
    <BaseTableBaseDataTableMobileCards :rows="props.users" :row-key="(row: UserViewData) => row.id" :fields="mobileFields" :actions="actions">
        <template #mobile-header="slotProps">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="resolveSlotRow(slotProps)" :show-email="true" />
            </div>
        </template>
    </BaseTableBaseDataTableMobileCards>

    <BaseTableBaseDataTable v-bind="dataTableProps" @sort="emit('sort', $event)">
        <template #cell-name="slotProps">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="resolveSlotRow(slotProps)" :show-email="true" />
                <span v-if="isCurrentUser(resolveSlotRow(slotProps))" class="text-xs text-muted-foreground">(you)</span>
            </div>
        </template>
        <template #cell-role="slotProps">
            <span :class="roleClass(resolveSlotRow(slotProps))">{{ roleLabel(resolveSlotRow(slotProps)) }}</span>
        </template>
        <template #cell-status="slotProps">
            <span :class="statusClass(resolveSlotRow(slotProps))">{{ statusLabel(resolveSlotRow(slotProps)) }}</span>
        </template>
    </BaseTableBaseDataTable>
</template>
