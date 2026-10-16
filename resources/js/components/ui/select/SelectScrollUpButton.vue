<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectScrollUpButtonProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectScrollUpButton } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<SelectScrollUpButtonProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardedProps(delegatedProps)
</script>

<template>
  <SelectScrollUpButton
    data-slot="select-scroll-up-button"
    v-bind="forwardedProps"
    :class="cn('flex cursor-default items-center justify-center py-1', props.class)"
  >
    <slot>
      <IconLucideChevronUp class="size-4" />
    </slot>
  </SelectScrollUpButton>
</template>
