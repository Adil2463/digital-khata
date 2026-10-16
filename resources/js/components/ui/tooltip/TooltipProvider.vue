<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { TooltipProvider, type TooltipProviderProps } from 'reka-ui'

const props = withDefaults(defineProps<TooltipProviderProps>(), {
  delayDuration: 0,
})

const forwarded = useForwardedProps(props)
</script>

<template>
  <TooltipProvider v-bind="forwarded">
    <slot />
  </TooltipProvider>
</template>
