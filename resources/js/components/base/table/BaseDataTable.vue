<script setup lang="ts" generic="TData, TSort extends string = string">
    import type { DataTableColumn, DataTableRowAction, SortDirection } from '@/types/base-ui'
    import IconLucideArrowDown from '~icons/lucide/arrow-down'
    import IconLucideArrowUp from '~icons/lucide/arrow-up'
    import IconLucideArrowUpDown from '~icons/lucide/arrow-up-down'

    const theme = appTheme

    interface Props {
        rows: TData[]
        columns: Array<DataTableColumn<TData, TSort>>
        rowKey: (row: TData) => string | number
        actions?: Array<DataTableRowAction<TData>>
        sortBy?: TSort
        sortDirection?: SortDirection
        emptyMessage?: string
        tableLabel?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        actions: () => [],
        emptyMessage: 'No records found.',
        tableLabel: 'Data table'
    })

    const emit = defineEmits<{
        sort: [sortKey: TSort]
    }>()

    const isSortable = (column: DataTableColumn<TData, TSort>): boolean => column.sortable === true && column.sortKey !== undefined

    const resolveSortIcon = (column: DataTableColumn<TData, TSort>) => {
        if (!isSortable(column)) {
            return null
        }

        if (props.sortBy !== column.sortKey) {
            return IconLucideArrowUpDown
        }

        return props.sortDirection === 'asc' ? IconLucideArrowUp : IconLucideArrowDown
    }

    const resolveAriaSort = (column: DataTableColumn<TData, TSort>): 'ascending' | 'descending' | 'none' => {
        if (!isSortable(column)) {
            return 'none'
        }

        if (props.sortBy !== column.sortKey) {
            return 'none'
        }

        return props.sortDirection === 'asc' ? 'ascending' : 'descending'
    }
</script>

<template>
    <UiCard>
        <UiCardContent class="p-0">
            <div class="hidden md:block">
                <div class="relative overflow-x-auto">
                    <table class="w-full min-w-[700px]" :aria-label="props.tableLabel">
                        <thead class="border-b">
                            <tr class="hover:bg-transparent">
                                <th
                                    v-for="column in props.columns"
                                    :key="column.key"
                                    :class="[theme.table.headerCell, column.headerClass]"
                                    :aria-sort="resolveAriaSort(column)"
                                >
                                    <button
                                        v-if="isSortable(column)"
                                        type="button"
                                        :class="theme.table.sortableHeader"
                                        @click="emit('sort', column.sortKey as TSort)"
                                    >
                                        {{ column.label }}
                                        <component :is="resolveSortIcon(column)" class="size-4" />
                                    </button>
                                    <span v-else>{{ column.label }}</span>
                                </th>
                                <th v-if="props.actions.length > 0" :class="theme.table.actionsHeaderCell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.rows" :key="props.rowKey(row)" :class="theme.table.row">
                                <td v-for="column in props.columns" :key="column.key" :class="[theme.table.cell, column.class]">
                                    <slot :name="`cell-${column.key}`" :row="row">
                                        {{ column.value(row) }}
                                    </slot>
                                </td>
                                <td v-if="props.actions.length > 0" :class="theme.table.actionsCell">
                                    <slot name="actions" :row="row">
                                        <BaseMenuBaseActionMenu :actions="props.actions" :row="row" />
                                    </slot>
                                </td>
                            </tr>
                            <tr v-if="props.rows.length === 0">
                                <td :colspan="props.columns.length + (props.actions.length > 0 ? 1 : 0)" :class="theme.table.empty">
                                    {{ props.emptyMessage }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </UiCardContent>
    </UiCard>
</template>
