<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { PaginationRootEmits, PaginationRootProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { PaginationRoot } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<PaginationRootProps & {
  class?: HTMLAttributes["class"]
}>()
const emits = defineEmits<PaginationRootEmits>()

const delegatedProps = reactiveOmit(props, "class", "itemsPerPage")
const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <PaginationRoot
    v-slot="slotProps"
    data-slot="pagination"
    :items-per-page="props.itemsPerPage"
    v-bind="forwarded"
    :class="cn('mx-auto flex w-full justify-center', props.class)"
  >
    <slot v-bind="slotProps" />
  </PaginationRoot>
</template>
