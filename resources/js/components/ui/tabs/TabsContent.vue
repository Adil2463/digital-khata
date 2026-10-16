<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { TabsContent, type TabsContentProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<TabsContentProps & { class?: HTMLAttributes['class'] }>()

const forwarded = useForwardedProps(reactiveOmit(props, 'class'))
</script>

<template>
    <TabsContent data-slot="tabs-content" v-bind="forwarded" :class="cn('flex-1 outline-none', props.class)">
        <slot />
    </TabsContent>
</template>
