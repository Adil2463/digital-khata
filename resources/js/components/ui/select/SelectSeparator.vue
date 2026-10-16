<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core'
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectSeparatorProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { SelectSeparator } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<SelectSeparatorProps & { class?: HTMLAttributes["class"] }>()

const forwarded = useForwardedProps(reactiveOmit(props, "class"))
</script>

<template>
  <SelectSeparator
    data-slot="select-separator"
    v-bind="forwarded"
    :class="cn('bg-border pointer-events-none -mx-1 my-1 h-px', props.class)"
  />
</template>
