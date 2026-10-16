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
    data-slot="sidebar-group-action"
    data-sidebar="group-action"
    v-bind="primitiveProps"
    :class="cn(
      'text-sidebar-foreground ring-sidebar-ring hover:bg-sidebar-accent hover:text-sidebar-accent-foreground absolute top-3.5 right-3 flex aspect-square w-5 items-center justify-center rounded-md p-0 outline-hidden transition-transform focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0',
      'after:absolute after:-inset-2 md:after:hidden',
      'group-data-[collapsible=icon]:hidden',
      props.class,
    )"
  >
    <slot />
  </Primitive>
</template>
