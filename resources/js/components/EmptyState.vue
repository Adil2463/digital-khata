<script setup lang="ts">
    import type { Component } from 'vue'
    import IconLucideFileQuestionMark from '~icons/lucide/file-question-mark'
    import IconLucideFolderOpen from '~icons/lucide/folder-open'
    import IconLucideInbox from '~icons/lucide/inbox'
    import IconLucideSearch from '~icons/lucide/search'

    const theme = appTheme

    export interface EmptyStateProps {
        title?: string
        description?: string
        icon?: 'inbox' | 'search' | 'folder' | 'question'
        actionText?: string
    }

    withDefaults(defineProps<EmptyStateProps>(), {
        title: 'No data found',
        description: 'There are no items to display.',
        icon: 'inbox'
    })

    const emit = defineEmits<{
        (e: 'action'): void
    }>()

    const iconMap = {
        inbox: IconLucideInbox,
        search: IconLucideSearch,
        folder: IconLucideFolderOpen,
        question: IconLucideFileQuestionMark
    } satisfies Record<NonNullable<EmptyStateProps['icon']>, Component>
</script>

<template>
    <div :class="theme.feedback.empty">
        <div :class="theme.feedback.emptyIcon">
            <component :is="iconMap[icon]" class="size-8" />
        </div>
        <h3 :class="theme.feedback.emptyTitle">
            {{ title }}
        </h3>
        <p :class="theme.feedback.emptyDescription">
            {{ description }}
        </p>
        <BaseButton v-if="actionText" class="mt-6" :label="actionText" @click="emit('action')" />
        <slot name="action" />
    </div>
</template>
