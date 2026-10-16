<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { Separator, type SeparatorProps } from 'reka-ui'

const props = withDefaults(defineProps<
  SeparatorProps & { class?: HTMLAttributes['class'] }
>(), {
  orientation: 'horizontal',
  decorative: true,
})

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <Separator
    data-slot="separator-root"
    v-bind="forwarded"
    :class="
      cn(
        `bg-border shrink-0 data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px`,
        props.class,
      )
    "
  />
</template>
