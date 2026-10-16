<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DropdownMenuSeparator, type DropdownMenuSeparatorProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuSeparatorProps & {
  class?: HTMLAttributes['class']
}>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <DropdownMenuSeparator
    data-slot="dropdown-menu-separator"
    v-bind="forwarded"
    :class="cn(appTheme.floating.separator, props.class)"
  />
</template>
