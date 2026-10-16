<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { DropdownMenuGroup, type DropdownMenuGroupProps } from 'reka-ui'

const props = defineProps<DropdownMenuGroupProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <DropdownMenuGroup
    data-slot="dropdown-menu-group"
    v-bind="forwarded"
  >
    <slot />
  </DropdownMenuGroup>
</template>
