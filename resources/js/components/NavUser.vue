<script setup lang="ts">
    import { useSidebar } from '@/components/ui/sidebar/utils'

    const user = useAuthUser({ required: true, context: 'NavUser' })

    const { isMobile, state } = useSidebar()
</script>

<template>
    <UiSidebarMenu>
        <UiSidebarMenuItem>
            <UiDropdownMenu>
                <UiDropdownMenuTrigger as-child>
                    <UiSidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <UserInfo :user="user" />
                        <IconLucideChevronsUpDown class="ml-auto size-4" />
                    </UiSidebarMenuButton>
                </UiDropdownMenuTrigger>
                <UiDropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </UiDropdownMenuContent>
            </UiDropdownMenu>
        </UiSidebarMenuItem>
    </UiSidebarMenu>
</template>
