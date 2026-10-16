<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { CollapsibleRootEmits, CollapsibleRootProps } from 'reka-ui'
import { CollapsibleRoot } from 'reka-ui'

const props = defineProps<CollapsibleRootProps>()
const emits = defineEmits<CollapsibleRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
  <CollapsibleRoot
    v-slot="{ open }"
    data-slot="collapsible"
    v-bind="forwarded"
  >
    <slot :open="open" />
  </CollapsibleRoot>
</template>
