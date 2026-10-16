<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectValueProps } from "reka-ui"
import { SelectValue } from "reka-ui"

const props = defineProps<SelectValueProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <SelectValue
    data-slot="select-value"
    v-bind="forwarded"
  >
    <slot />
  </SelectValue>
</template>
