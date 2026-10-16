<script setup lang="ts">
    import { type NavItem } from '@/types'

    const theme = appTheme

    interface Props {
        items: NavItem[]
        class?: string
    }

    defineProps<Props>()
</script>

<template>
    <UiSidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <UiSidebarGroupContent>
            <UiSidebarMenu>
                <UiSidebarMenuItem v-for="item in items" :key="item.title">
                    <UiSidebarMenuButton :class="theme.navigation.footerItem" :is-active="item.isActive === true" as-child>
                        <a v-if="item.external" :href="item.href" target="_blank" rel="noopener noreferrer">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                        <Link v-else :href="item.href" :aria-current="item.isActive ? 'page' : undefined">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </UiSidebarMenuButton>
                </UiSidebarMenuItem>
            </UiSidebarMenu>
        </UiSidebarGroupContent>
    </UiSidebarGroup>
</template>
