<script setup lang="ts" generic="TData">
    import type { DataTableRowAction, MobileCardField } from '@/types/base-ui'

    const theme = appTheme

    interface Props {
        rows: TData[]
        rowKey: (row: TData) => string | number
        fields: Array<MobileCardField<TData>>
        actions?: Array<DataTableRowAction<TData>>
        emptyMessage?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        actions: () => [],
        emptyMessage: 'No records found.'
    })
</script>

<template>
    <div :class="theme.table.mobileGrid">
        <article v-for="row in props.rows" :key="props.rowKey(row)" :class="theme.table.mobileCard">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <slot name="mobile-header" :row="row" />
                </div>
                <BaseMenuBaseActionMenu v-if="props.actions.length > 0" :actions="props.actions" :row="row" />
            </div>

            <dl class="mt-4 grid gap-3 text-sm">
                <div v-for="field in props.fields" :key="field.key" class="grid gap-1">
                    <dt class="text-xs tracking-wide text-muted-foreground uppercase">{{ field.label }}</dt>
                    <dd :class="field.class">{{ field.value(row) }}</dd>
                </div>
            </dl>
        </article>

        <div v-if="props.rows.length === 0" :class="theme.table.mobileEmpty">
            {{ props.emptyMessage }}
        </div>
    </div>
</template>
