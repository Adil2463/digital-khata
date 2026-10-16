<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { SelectRootEmits, SelectRootProps } from "reka-ui"
import { SelectRoot } from "reka-ui"

const props = defineProps<SelectRootProps>()
const emits = defineEmits<SelectRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <SelectRoot
    data-slot="select"
    v-bind="forwarded"
  >
    <slot />
  </SelectRoot>
</template>
