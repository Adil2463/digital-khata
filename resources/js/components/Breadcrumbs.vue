<script setup lang="ts">
    import type { BreadcrumbItem } from '@/types'

    defineProps<{
        breadcrumbs: BreadcrumbItem[]
    }>()
</script>

<template>
    <UiBreadcrumb class="min-w-0">
        <UiBreadcrumbList class="min-w-0 flex-nowrap overflow-hidden">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <UiBreadcrumbItem :class="index === breadcrumbs.length - 1 ? 'min-w-0' : 'shrink-0'">
                    <template v-if="index === breadcrumbs.length - 1">
                        <UiBreadcrumbPage class="truncate">{{ item.title }}</UiBreadcrumbPage>
                    </template>
                    <template v-else>
                        <UiBreadcrumbLink as-child class="truncate">
                            <Link class="truncate" :href="item.href ?? '#'">{{ item.title }}</Link>
                        </UiBreadcrumbLink>
                    </template>
                </UiBreadcrumbItem>
                <UiBreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" class="shrink-0" />
            </template>
        </UiBreadcrumbList>
    </UiBreadcrumb>
</template>
