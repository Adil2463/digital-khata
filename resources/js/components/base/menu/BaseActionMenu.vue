<script setup lang="ts" generic="TRow">
    import type { DataTableRowAction } from '@/types/base-ui'

    interface Props {
        actions: Array<DataTableRowAction<TRow>>
        row: TRow
        triggerLabel?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        triggerLabel: 'Open actions'
    })

    const isVisible = (action: DataTableRowAction<TRow>): boolean => {
        if (typeof action.visible === 'function') {
            return action.visible(props.row)
        }

        return action.visible ?? true
    }

    const isDisabled = (action: DataTableRowAction<TRow>): boolean => {
        if (typeof action.disabled === 'function') {
            return action.disabled(props.row)
        }

        return action.disabled ?? false
    }

    const handleAction = (action: DataTableRowAction<TRow>): void => {
        if (isDisabled(action)) {
            return
        }

        action.onClick(props.row)
    }
</script>

<template>
    <UiDropdownMenu>
        <UiDropdownMenuTrigger as-child>
            <BaseButton
                variant="ghost"
                size="icon"
                class="h-8 w-8 p-0 focus-visible:ring-2 focus-visible:ring-ring/70"
                :aria-label="props.triggerLabel"
                :title="props.triggerLabel"
            >
                <span class="sr-only">{{ props.triggerLabel }}</span>
                <IconLucideEllipsisVertical class="size-4" />
            </BaseButton>
        </UiDropdownMenuTrigger>
        <UiDropdownMenuContent align="end">
            <UiDropdownMenuItem
                v-for="action in props.actions"
                v-show="isVisible(action)"
                :key="action.key"
                :variant="action.destructive ? 'destructive' : 'default'"
                :disabled="isDisabled(action)"
                @click="handleAction(action)"
            >
                <component :is="action.icon" v-if="action.icon !== undefined" class="mr-2 size-4" />
                {{ action.label }}
            </UiDropdownMenuItem>
        </UiDropdownMenuContent>
    </UiDropdownMenu>
</template>
