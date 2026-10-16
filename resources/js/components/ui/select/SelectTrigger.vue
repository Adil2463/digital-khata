<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { SelectTriggerProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectIcon, SelectTrigger } from "reka-ui"
import { cn } from "@/lib/utils"
import { appTheme } from '@/lib/theme'

const props = withDefaults(
  defineProps<SelectTriggerProps & { class?: HTMLAttributes["class"], size?: "sm" | "default" }>(),
  { size: "default" },
)

const delegatedProps = reactiveOmit(props, "class", "size")
const forwardedProps = useForwardedProps(delegatedProps)
</script>

<template>
  <SelectTrigger
    data-slot="select-trigger"
    :data-size="size"
    v-bind="forwardedProps"
    :class="cn(
      appTheme.select.trigger,
      props.class,
    )"
  >
    <slot />
    <SelectIcon as-child>
      <IconLucideChevronDown class="size-4 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>
