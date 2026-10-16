<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { AvatarImageProps } from 'reka-ui'
import { AvatarImage } from 'reka-ui'

const props = defineProps<AvatarImageProps>()

const forwardedProps = useForwardedProps(reactiveOmit(props, 'src'))
</script>

<template>
  <AvatarImage
    data-slot="avatar-image"
    :src="props.src"
    v-bind="forwardedProps"
    class="aspect-square size-full"
  >
    <slot />
  </AvatarImage>
</template>
