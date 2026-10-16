<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core'
import { useForwardedProps } from '@/lib/forward-props'
import type { PaginationListItemProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { PaginationListItem } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'

const props = withDefaults(defineProps<PaginationListItemProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
  isActive?: boolean
}>(), {
  size: "icon",
})

const forwarded = useForwardedProps(reactiveOmit(props, "class", "size", "isActive", "value"))
</script>

<template>
  <PaginationListItem
    data-slot="pagination-item"
    :value="props.value"
    v-bind="forwarded"
    :class="cn(
      buttonStyles({
        variant: isActive ? 'outline' : 'ghost',
        size,
      }),
      props.class)"
  >
    <slot />
  </PaginationListItem>
</template>
