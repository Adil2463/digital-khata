<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { HTMLAttributes } from 'vue'
import type { ToastRootEmits, ToastRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { toastStyles, type ToastVariant } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { ToastRoot } from 'reka-ui'

interface Props extends ToastRootProps {
    class?: HTMLAttributes['class']
    variant?: ToastVariant
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
})

const emits = defineEmits<ToastRootEmits>()

const delegatedProps = reactiveOmit(props, 'class', 'variant')
const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
    <ToastRoot data-slot="toast" v-bind="forwarded" :class="cn(toastStyles(props.variant), props.class)">
        <slot />
    </ToastRoot>
</template>
