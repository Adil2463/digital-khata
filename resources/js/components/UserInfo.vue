<script setup lang="ts">
    import type { UserViewData } from '@/types/app-data'

    interface Props {
        user: UserViewData
        showEmail?: boolean
    }

    withDefaults(defineProps<Props>(), {
        showEmail: false
    })

    const { getInitials } = useInitials()
</script>

<template>
    <UiAvatar class="h-8 w-8 overflow-hidden rounded-lg">
        <UiAvatarFallback class="rounded-lg text-foreground">
            {{ getInitials(user.name) }}
        </UiAvatarFallback>
    </UiAvatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
