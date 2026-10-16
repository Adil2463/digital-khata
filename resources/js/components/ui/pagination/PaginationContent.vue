<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core'
import { useForwardedProps } from '@/lib/forward-props'
import type { PaginationListProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { PaginationList } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<PaginationListProps & { class?: HTMLAttributes["class"] }>()

const forwarded = useForwardedProps(reactiveOmit(props, "class"))
</script>

<template>
  <PaginationList
    v-slot="slotProps"
    data-slot="pagination-content"
    v-bind="forwarded"
    :class="cn('flex flex-row items-center gap-1', props.class)"
  >
    <slot v-bind="slotProps" />
  </PaginationList>
</template>
