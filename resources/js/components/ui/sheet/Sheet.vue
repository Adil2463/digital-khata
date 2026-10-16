<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { DialogRoot, type DialogRootEmits, type DialogRootProps } from 'reka-ui'

const props = defineProps<DialogRootProps>()
const emits = defineEmits<DialogRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <DialogRoot
    data-slot="sheet"
    v-bind="forwarded"
  >
    <slot />
  </DialogRoot>
</template>
