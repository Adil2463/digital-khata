<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core'
import { useForwardedProps } from '@/lib/forward-props'
import type { PaginationEllipsisProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { PaginationEllipsis } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<PaginationEllipsisProps & { class?: HTMLAttributes["class"] }>()

const forwarded = useForwardedProps(reactiveOmit(props, "class"))
</script>

<template>
  <PaginationEllipsis
    data-slot="pagination-ellipsis"
    v-bind="forwarded"
    :class="cn('flex size-9 items-center justify-center', props.class)"
  >
    <slot>
      <IconLucideEllipsis class="size-4" />
      <span class="sr-only">More pages</span>
    </slot>
  </PaginationEllipsis>
</template>
