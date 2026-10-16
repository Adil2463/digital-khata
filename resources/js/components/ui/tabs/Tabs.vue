<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { TabsRootEmits, TabsRootProps } from 'reka-ui'
import { TabsRoot } from 'reka-ui'

const props = defineProps<TabsRootProps>()
const emits = defineEmits<TabsRootEmits>()

const forwarded = useForwardedPropsEmits(props, emits)
</script>

<template>
    <TabsRoot data-slot="tabs" v-bind="forwarded">
        <slot />
    </TabsRoot>
</template>
