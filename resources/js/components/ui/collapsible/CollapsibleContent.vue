<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { CollapsibleContent, type CollapsibleContentProps } from 'reka-ui'

const props = defineProps<CollapsibleContentProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
  <CollapsibleContent
    data-slot="collapsible-content"
    v-bind="forwarded"
  >
    <slot />
  </CollapsibleContent>
</template>
