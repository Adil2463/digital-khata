<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { ToastCloseProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { ToastClose } from 'reka-ui'

const props = defineProps<
    ToastCloseProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <ToastClose
        data-slot="toast-close"
        v-bind="forwarded"
        :class="cn(appTheme.toast.close, props.class)"
    >
        <slot>
            <IconLucideX class="size-4" />
        </slot>
    </ToastClose>
</template>
