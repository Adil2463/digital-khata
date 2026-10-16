<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { PaginationFirstProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationFirst } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationFirstProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
  <PaginationFirst
    data-slot="pagination-first"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <IconLucideChevronsLeft />
      <span class="hidden sm:block">First</span>
    </slot>
  </PaginationFirst>
</template>
