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
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
