<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { appTheme } from '@/lib/theme'
import { DropdownMenuItemIndicator, DropdownMenuRadioItem, type DropdownMenuRadioItemEmits, type DropdownMenuRadioItemProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuRadioItemProps & { class?: HTMLAttributes['class'] }>()

const emits = defineEmits<DropdownMenuRadioItemEmits>()

const forwarded = useForwardedPropsEmits(reactiveOmit(props, 'class', 'value'), emits)
</script>

<template>
  <DropdownMenuRadioItem
    data-slot="dropdown-menu-radio-item"
    :value="props.value"
    v-bind="forwarded"
    :class="cn(
      appTheme.floating.item,
      'pr-2 pl-8',
      props.class,
    )"
  >
    <span class="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center">
      <DropdownMenuItemIndicator>
        <IconLucideCircle class="size-2 fill-current" />
      </DropdownMenuItemIndicator>
    </span>
    <slot />
  </DropdownMenuRadioItem>
</template>
