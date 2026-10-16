<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { NavigationMenuLink, type NavigationMenuLinkEmits, type NavigationMenuLinkProps } from 'reka-ui'

const props = defineProps<NavigationMenuLinkProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<NavigationMenuLinkEmits>()

const delegatedProps = reactiveOmit(props, 'class')
const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuLink
    data-slot="navigation-menu-link"
    v-bind="forwarded"
    :class="cn(appTheme.navigation.link, props.class)"
  >
    <slot />
  </NavigationMenuLink>
</template>
