<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { DialogClose, type DialogCloseProps } from 'reka-ui'

const props = defineProps<DialogCloseProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <DialogClose
    data-slot="sheet-close"
    v-bind="forwarded"
  >
    <slot />
  </DialogClose>
</template>
