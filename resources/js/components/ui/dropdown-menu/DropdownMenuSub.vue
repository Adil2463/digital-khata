<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { DropdownMenuSub, type DropdownMenuSubEmits, type DropdownMenuSubProps } from 'reka-ui'

const props = defineProps<DropdownMenuSubProps>()
const emits = defineEmits<DropdownMenuSubEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuSub data-slot="dropdown-menu-sub" v-bind="forwarded">
    <slot />
  </DropdownMenuSub>
</template>
