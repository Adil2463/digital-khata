<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { ToastProviderProps } from 'reka-ui'
import { ToastProvider } from 'reka-ui'

const props = defineProps<ToastProviderProps>()
const forwarded = useForwardedProps(props)
</script>

<template>
    <ToastProvider data-slot="toast-provider" v-bind="forwarded">
        <slot />
    </ToastProvider>
</template>
