<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { DialogTrigger, type DialogTriggerProps } from 'reka-ui'

const props = defineProps<DialogTriggerProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <DialogTrigger
    data-slot="sheet-trigger"
    v-bind="forwarded"
  >
    <slot />
  </DialogTrigger>
</template>
