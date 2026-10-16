<script setup lang="ts">
    interface Props {
        variant?: 'header' | 'sidebar'
    }

    defineProps<Props>()

    const page = useAppPage()
    const isOpen = computed(() => page.props.sidebarOpen)
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <UiSidebarProvider v-else :default-open="isOpen">
        <slot />
    </UiSidebarProvider>
</template>
