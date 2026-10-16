<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { SelectContentEmits, SelectContentProps } from "reka-ui"
import { type HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectContent, SelectPortal, SelectViewport } from "reka-ui"
import { cn } from "@/lib/utils"
import { appTheme } from '@/lib/theme'
import { SelectScrollDownButton, SelectScrollUpButton } from "."

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(
  defineProps<SelectContentProps & { class?: HTMLAttributes["class"] }>(),
  {
    position: "popper",
  },
)
const emits = defineEmits<SelectContentEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <SelectPortal>
    <SelectContent
      data-slot="select-content"
      v-bind="{ ...forwarded, ...$attrs }"
      :class="cn(
        appTheme.select.content,
        position === 'popper'
          && appTheme.select.popper,
        props.class,
      )
      "
    >
      <SelectScrollUpButton />
      <SelectViewport :class="cn(appTheme.select.viewport, position === 'popper' && appTheme.select.popperViewport)">
        <slot />
      </SelectViewport>
      <SelectScrollDownButton />
    </SelectContent>
  </SelectPortal>
</template>
