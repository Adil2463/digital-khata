<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { NavigationMenuContent, type NavigationMenuContentEmits, type NavigationMenuContentProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<NavigationMenuContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<NavigationMenuContentEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuContent
    data-slot="navigation-menu-content"
    v-bind="forwarded"
    :class="cn(
      appTheme.navigation.content,
      props.class,
    )"
  >
    <slot />
  </NavigationMenuContent>
</template>
