<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DropdownMenuSubContent, type DropdownMenuSubContentEmits, type DropdownMenuSubContentProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuSubContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DropdownMenuSubContentEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <DropdownMenuSubContent
    data-slot="dropdown-menu-sub-content"
    v-bind="forwarded"
    :class="cn(appTheme.floating.content, 'origin-(--reka-dropdown-menu-content-transform-origin)', props.class)"
  >
    <slot />
  </DropdownMenuSubContent>
</template>
