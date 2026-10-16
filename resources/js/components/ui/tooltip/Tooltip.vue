<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { TooltipRoot, type TooltipRootEmits, type TooltipRootProps } from 'reka-ui'

const props = defineProps<TooltipRootProps>()
const emits = defineEmits<TooltipRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <TooltipRoot
    data-slot="tooltip"
    v-bind="forwarded"
  >
    <slot />
  </TooltipRoot>
</template>
