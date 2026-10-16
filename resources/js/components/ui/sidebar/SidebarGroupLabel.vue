<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive } from 'reka-ui'

const props = defineProps<PrimitiveProps & {
  class?: HTMLAttributes['class']
}>()

const primitiveProps = computed(() => {
  const forwarded: {
    as?: Exclude<PrimitiveProps['as'], undefined>
    asChild?: Exclude<PrimitiveProps['asChild'], undefined>
  } = {}

  if (props.as !== undefined) {
    forwarded.as = props.as
  }

  if (props.asChild !== undefined) {
    forwarded.asChild = props.asChild
  }

  return forwarded
})
</script>

<template>
  <Primitive
    data-slot="sidebar-group-label"
    data-sidebar="group-label"
    v-bind="primitiveProps"
    :class="cn(
      'text-sidebar-foreground/70 ring-sidebar-ring flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium outline-hidden transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0',
      'group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0',
      props.class)"
  >
    <slot />
  </Primitive>
</template>
