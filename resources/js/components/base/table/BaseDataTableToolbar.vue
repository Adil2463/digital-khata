<script setup lang="ts">
    const theme = appTheme

    interface Props {
        searchValue: string
        searchPlaceholder?: string
        searchLabel?: string
        perPage?: number
        perPageOptions?: number[]
        perPageLabel?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        searchPlaceholder: 'Search...',
        searchLabel: 'Search records',
        perPageOptions: () => [10, 25, 50, 100],
        perPageLabel: 'Rows per page'
    })

    const emit = defineEmits<{
        'update:searchValue': [value: string]
        'update:perPage': [value: number]
    }>()
</script>

<template>
    <div :class="theme.table.toolbar">
        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
            <UiInput
                :model-value="props.searchValue"
                class="w-full sm:w-72"
                type="search"
                :placeholder="props.searchPlaceholder"
                :aria-label="props.searchLabel"
                @update:model-value="emit('update:searchValue', String($event))"
            />
            <UiSelect
                v-if="props.perPage !== undefined"
                :model-value="String(props.perPage)"
                @update:model-value="emit('update:perPage', Number($event))"
            >
                <UiSelectTrigger class="w-full sm:w-40" :aria-label="props.perPageLabel">
                    <UiSelectValue />
                </UiSelectTrigger>
                <UiSelectContent>
                    <UiSelectItem v-for="option in props.perPageOptions" :key="option" :value="String(option)"> {{ option }} per page </UiSelectItem>
                </UiSelectContent>
            </UiSelect>
        </div>

        <slot name="actions" />
    </div>
</template>
