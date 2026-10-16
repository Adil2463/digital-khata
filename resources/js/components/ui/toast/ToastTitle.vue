<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { ToastTitleProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastTitle } from 'reka-ui'

const props = defineProps<
    ToastTitleProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <ToastTitle data-slot="toast-title" v-bind="forwarded" :class="cn('text-sm font-semibold', props.class)">
        <slot />
    </ToastTitle>
</template>
