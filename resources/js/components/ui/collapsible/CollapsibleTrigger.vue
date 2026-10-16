<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { CollapsibleTrigger, type CollapsibleTriggerProps } from 'reka-ui'

const props = defineProps<CollapsibleTriggerProps>()

const forwarded = useForwardedProps(props)
</script>

<template>
    <CollapsibleTrigger data-slot="collapsible-trigger" v-bind="forwarded">
        <slot />
    </CollapsibleTrigger>
</template>
