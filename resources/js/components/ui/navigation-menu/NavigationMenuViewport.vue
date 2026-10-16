<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { NavigationMenuViewport, type NavigationMenuViewportProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<NavigationMenuViewportProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')

const forwardedProps = useForwardedProps(delegatedProps)
</script>

<template>
  <div class="absolute top-full left-0 isolate z-50 flex justify-center">
    <NavigationMenuViewport
      data-slot="navigation-menu-viewport"
      v-bind="forwardedProps"
      :class="
        cn(
          appTheme.navigation.viewport,
          props.class,
        )
      "
    />
  </div>
</template>
