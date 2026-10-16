<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { appTheme } from '@/lib/theme'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { DialogClose, DialogContent, type DialogContentEmits, type DialogContentProps, DialogPortal } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import DialogOverlay from './DialogOverlay.vue'

const props = defineProps<DialogContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DialogContentEmits>()

const forwarded = useForwardedPropsEmits(reactiveOmit(props, 'class'), emits)
</script>

<template>
    <DialogPortal>
        <DialogOverlay />
        <DialogContent data-slot="dialog-content" v-bind="forwarded" :class="cn(appTheme.dialog.content, props.class)">
            <slot />

            <DialogClose :class="appTheme.dialog.close">
                <IconLucideX />
                <span class="sr-only">Close</span>
            </DialogClose>
        </DialogContent>
    </DialogPortal>
</template>
