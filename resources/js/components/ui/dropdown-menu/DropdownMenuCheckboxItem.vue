<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DropdownMenuCheckboxItem, type DropdownMenuCheckboxItemEmits, type DropdownMenuCheckboxItemProps, DropdownMenuItemIndicator } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuCheckboxItemProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DropdownMenuCheckboxItemEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <DropdownMenuCheckboxItem
    data-slot="dropdown-menu-checkbox-item"
    v-bind="forwarded"
    :class=" cn(
      appTheme.floating.item,
      'pr-2 pl-8',
      props.class,
    )"
  >
    <span class="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center">
      <DropdownMenuItemIndicator>
        <IconLucideCheck class="size-4" />
      </DropdownMenuItemIndicator>
    </span>
    <slot />
  </DropdownMenuCheckboxItem>
</template>
