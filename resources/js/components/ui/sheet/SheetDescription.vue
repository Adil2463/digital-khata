<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { DialogDescription, type DialogDescriptionProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <DialogDescription
    data-slot="sheet-description"
    :class="cn('text-muted-foreground text-sm', props.class)"
    v-bind="forwarded"
  >
    <slot />
  </DialogDescription>
</template>
