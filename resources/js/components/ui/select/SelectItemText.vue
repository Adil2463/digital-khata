<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectItemTextProps } from "reka-ui"
import { SelectItemText } from "reka-ui"

const props = defineProps<SelectItemTextProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <SelectItemText
    data-slot="select-item-text"
    v-bind="forwarded"
  >
    <slot />
  </SelectItemText>
</template>
