<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { DropdownMenuRoot, type DropdownMenuRootEmits, type DropdownMenuRootProps } from 'reka-ui'

const props = defineProps<DropdownMenuRootProps>()
const emits = defineEmits<DropdownMenuRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRoot
    data-slot="dropdown-menu"
    v-bind="forwarded"
  >
    <slot />
  </DropdownMenuRoot>
</template>
