<script setup lang="ts">
    import type { BreadcrumbItem } from '@/types'

    withDefaults(
        defineProps<{
            breadcrumbs?: BreadcrumbItem[]
        }>(),
        {
            breadcrumbs: () => []
        }
    )

    const shop = useCurrentShop()
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center border-b border-sidebar-border/70 px-3 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 sm:px-4"
    >
        <div class="flex min-w-0 flex-1 items-center gap-2">
            <UiSidebarTrigger class="-ml-1 shrink-0" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs class="hidden min-w-0 sm:block" :breadcrumbs="breadcrumbs" />
            </template>
            <span v-if="shop" class="min-w-0 truncate text-sm font-medium sm:hidden">{{ shop.name }}</span>
        </div>
    </header>
</template>
