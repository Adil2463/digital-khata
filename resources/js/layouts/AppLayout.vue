<script setup lang="ts">
    import type { BreadcrumbItem } from '@/types'

    interface Props {
        breadcrumbs?: BreadcrumbItem[]
    }

    withDefaults(defineProps<Props>(), {
        breadcrumbs: () => []
    })
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>
</template>
