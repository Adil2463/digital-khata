<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { DropdownMenuRadioGroup, type DropdownMenuRadioGroupEmits, type DropdownMenuRadioGroupProps } from 'reka-ui'

const props = defineProps<DropdownMenuRadioGroupProps>()
const emits = defineEmits<DropdownMenuRadioGroupEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRadioGroup
    data-slot="dropdown-menu-radio-group"
    v-bind="forwarded"
  >
    <slot />
  </DropdownMenuRadioGroup>
</template>
