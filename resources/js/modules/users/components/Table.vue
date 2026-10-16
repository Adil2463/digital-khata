<script setup lang="ts">
    import type { UserViewData } from '@/types/app-data'
    import type { DataTableColumn, DataTableRowAction, MobileCardField } from '@/types/base-ui'
    import type { UserSortColumn } from '../contracts/types'

    interface Props {
        currentUserId: number
        users: UserViewData[]
        sortBy?: UserSortColumn
        sortDirection?: 'asc' | 'desc'
    }

    const props = defineProps<Props>()

    const emit = defineEmits<{
        delete: [user: UserViewData]
        edit: [user: UserViewData]
        sort: [column: UserSortColumn]
    }>()

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
            class: 'capitalize',
            value: (row) => capitalize(row.role)
        },
        {
            key: 'email',
            label: 'Email',
            sortable: true,
            sortKey: 'email',
            value: (row) => row.email
        },
        {
            key: 'created_at',
            label: 'Created At',
            sortable: true,
            sortKey: 'created_at',
            value: (row) => formatDate(row.created_at)
        }
    ]

    const mobileFields: Array<MobileCardField<UserViewData>> = [
        {
            key: 'role',
            label: 'Role',
            value: (row) => capitalize(row.role)
        },
        {
            key: 'email',
            label: 'Email',
            class: 'break-all',
            value: (row) => row.email
        },
        {
            key: 'created_at',
            label: 'Created',
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
            key: 'delete',
            label: 'Delete',
            destructive: true,
            visible: (row) => props.currentUserId !== row.id,
            onClick: (row) => emit('delete', row)
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
            emptyMessage: 'No users found.'
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
                <UserInfo :user="resolveSlotRow(slotProps)" />
            </div>
        </template>
    </BaseTableBaseDataTableMobileCards>

    <BaseTableBaseDataTable v-bind="dataTableProps" @sort="emit('sort', $event)">
        <template #cell-name="slotProps">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="resolveSlotRow(slotProps)" />
            </div>
        </template>
    </BaseTableBaseDataTable>
</template>
