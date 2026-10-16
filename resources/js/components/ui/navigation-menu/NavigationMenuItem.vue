<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { NavigationMenuItem, type NavigationMenuItemProps } from 'reka-ui'

const props = defineProps<NavigationMenuItemProps & { class?: HTMLAttributes['class'] }>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <NavigationMenuItem
    data-slot="navigation-menu-item"
    v-bind="forwarded"
    :class="cn('relative', props.class)"
  >
    <slot />
  </NavigationMenuItem>
</template>
