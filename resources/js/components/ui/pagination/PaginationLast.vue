<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { PaginationLastProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationLast } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationLastProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
  <PaginationLast
    data-slot="pagination-last"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <span class="hidden sm:block">Last</span>
      <IconLucideChevronsRight />
    </slot>
  </PaginationLast>
</template>
