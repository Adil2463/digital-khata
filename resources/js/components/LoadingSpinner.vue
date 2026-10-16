<script setup lang="ts">
    const theme = appTheme

    export interface LoadingSpinnerProps {
        size?: 'sm' | 'md' | 'lg' | 'xl'
        color?: 'primary' | 'inverse' | 'muted'
    }

    withDefaults(defineProps<LoadingSpinnerProps>(), {
        size: 'md',
        color: 'primary'
    })
</script>

<template>
    <IconLucideLoaderCircle
        :class="['animate-spin', theme.feedback.spinnerSize[size], theme.feedback.spinnerColor[color]]"
        aria-label="Loading"
        role="status"
    />
</template>
