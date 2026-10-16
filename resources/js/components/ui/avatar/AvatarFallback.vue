<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { AvatarFallback, type AvatarFallbackProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<AvatarFallbackProps & { class?: HTMLAttributes['class'] }>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
  <AvatarFallback
    data-slot="avatar-fallback"
    v-bind="forwarded"
    :class="cn('bg-muted flex size-full items-center justify-center rounded-full', props.class)"
  >
    <slot />
  </AvatarFallback>
</template>
