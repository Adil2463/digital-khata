<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectGroupProps } from "reka-ui"
import { SelectGroup } from "reka-ui"

const props = defineProps<SelectGroupProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <SelectGroup
    data-slot="select-group"
    v-bind="forwarded"
  >
    <slot />
  </SelectGroup>
</template>
