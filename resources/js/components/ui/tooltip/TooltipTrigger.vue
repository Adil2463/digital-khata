<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { TooltipTrigger, type TooltipTriggerProps } from 'reka-ui'

const props = defineProps<TooltipTriggerProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <TooltipTrigger
    data-slot="tooltip-trigger"
    v-bind="forwarded"
  >
    <slot />
  </TooltipTrigger>
</template>
