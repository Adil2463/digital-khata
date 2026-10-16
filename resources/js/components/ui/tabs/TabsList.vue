<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { TabsListProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { TabsList } from 'reka-ui'

const props = defineProps<
    TabsListProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <TabsList
        data-slot="tabs-list"
        v-bind="forwarded"
        :class="cn(appTheme.field.tabsList, props.class)"
    >
        <slot />
    </TabsList>
</template>
