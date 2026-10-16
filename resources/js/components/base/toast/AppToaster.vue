<script setup lang="ts">
    import { useMounted } from '@vueuse/core'

    const { toasts, dismiss } = useToast()

    useFlashToasts()

    // The server never raises toasts, so the first client render has to stay
    // empty too; anything already queued during setup appears right after mount.
    const isMounted = useMounted()
    const visibleToasts = computed(() => (isMounted.value ? toasts.value : []))
</script>

<template>
    <UiToastProvider>
        <UiToast
            v-for="toast in visibleToasts"
            :key="toast.id"
            :open="true"
            :variant="toast.variant"
            @update:open="(open: boolean) => !open && dismiss(toast.id)"
        >
            <div class="grid gap-1">
                <UiToastTitle>{{ toast.title }}</UiToastTitle>
                <UiToastDescription v-if="toast.description !== undefined">{{ toast.description }}</UiToastDescription>
            </div>
            <UiToastClose />
        </UiToast>
        <UiToastViewport />
    </UiToastProvider>
</template>
