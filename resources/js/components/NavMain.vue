<script setup lang="ts">
    import { type NavItem } from '@/types'

    defineProps<{
        items: NavItem[]
    }>()
</script>

<template>
    <UiSidebarGroup class="px-2 py-0">
        <UiSidebarGroupLabel>Platform</UiSidebarGroupLabel>
        <UiSidebarMenu>
            <UiSidebarMenuItem v-for="item in items" :key="item.title">
                <UiSidebarMenuButton as-child :is-active="item.isActive === true" :tooltip="item.title">
                    <Link :href="item.href" :aria-current="item.isActive ? 'page' : undefined">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </UiSidebarMenuButton>
            </UiSidebarMenuItem>
        </UiSidebarMenu>
    </UiSidebarGroup>
</template>
