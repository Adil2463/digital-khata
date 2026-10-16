<script setup lang="ts">
    const theme = appTheme

    export interface LoadingOverlayProps {
        loading?: boolean
        text?: string
        fullscreen?: boolean
    }

    withDefaults(defineProps<LoadingOverlayProps>(), {
        loading: true,
        text: 'Loading...',
        fullscreen: false
    })
</script>

<template>
    <Transition
        :enter-active-class="theme.animation.fadeTransition"
        :leave-active-class="theme.animation.fadeTransition"
        :enter-from-class="theme.animation.fadeHidden"
        :leave-to-class="theme.animation.fadeHidden"
    >
        <div
            v-if="loading"
            :class="[theme.feedback.loadingOverlay, fullscreen ? 'fixed inset-0 z-50' : 'absolute inset-0 rounded-panel']"
            role="status"
            aria-live="polite"
        >
            <LoadingSpinner size="lg" />
            <span v-if="text" :class="theme.feedback.loadingText">
                {{ text }}
            </span>
        </div>
    </Transition>
</template>
