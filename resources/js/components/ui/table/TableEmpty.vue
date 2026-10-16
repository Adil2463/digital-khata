<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { cn } from "@/lib/utils"
import TableCell from "./TableCell.vue"
import TableRow from "./TableRow.vue"

const props = withDefaults(defineProps<{
  class?: HTMLAttributes["class"]
  colspan?: number
}>(), {
  colspan: 1,
})

const forwarded = useForwardedProps(reactiveOmit(props, "class"))
</script>

<template>
  <TableRow>
    <TableCell
      :class="
        cn(
          'p-4 whitespace-nowrap align-middle text-sm text-foreground',
          props.class,
        )
      "
      v-bind="forwarded"
    >
      <div class="flex items-center justify-center py-10">
        <slot />
      </div>
    </TableCell>
  </TableRow>
</template>
