<script setup lang="ts">
    const page = useAppPage()

    // Page state is only initialized once the Inertia page component has run its own
    // setup, so the watch starts after mount. It never runs during SSR, where there is
    // no client query cache to protect.
    onMounted(() => {
        watch(
            () => page.props.auth.user?.id,
            () => clearApiQueryCache(),
            { flush: 'sync' }
        )
    })
</script>

<template>
    <slot />
    <BaseToastAppToaster />
</template>
