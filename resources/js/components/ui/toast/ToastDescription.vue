<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { ToastDescriptionProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastDescription } from 'reka-ui'

const props = defineProps<
    ToastDescriptionProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <ToastDescription data-slot="toast-description" v-bind="forwarded" :class="cn('text-sm opacity-90', props.class)">
        <slot />
    </ToastDescription>
</template>
