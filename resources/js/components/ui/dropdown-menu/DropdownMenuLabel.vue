<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuLabel, type DropdownMenuLabelProps } from 'reka-ui'

const props = defineProps<DropdownMenuLabelProps & { class?: HTMLAttributes['class'], inset?: boolean }>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardedProps(delegatedProps)
</script>

<template>
  <DropdownMenuLabel
    data-slot="dropdown-menu-label"
    :data-inset="inset ? '' : undefined"
    v-bind="forwardedProps"
    :class="cn(appTheme.floating.label, props.class)"
  >
    <slot />
  </DropdownMenuLabel>
</template>
