<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DialogOverlay, type DialogOverlayProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DialogOverlayProps & { class?: HTMLAttributes['class'] }>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <DialogOverlay
    data-slot="dialog-overlay"
    v-bind="forwarded"
    :class="cn(appTheme.overlay, props.class)"
  >
    <slot />
  </DialogOverlay>
</template>
