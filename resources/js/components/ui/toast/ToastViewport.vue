<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { ToastViewportProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastViewport } from 'reka-ui'

const props = defineProps<
    ToastViewportProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <ToastViewport
        data-slot="toast-viewport"
        v-bind="forwarded"
        :class="cn('fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse gap-2 p-4 sm:top-auto sm:right-0 sm:bottom-0 sm:flex-col md:max-w-[420px]', props.class)"
    />
</template>
