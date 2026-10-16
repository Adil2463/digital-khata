<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { TabsTriggerProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { TabsTrigger } from 'reka-ui'

const props = defineProps<
    TabsTriggerProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <TabsTrigger
        data-slot="tabs-trigger"
        v-bind="forwarded"
        :class="
            cn(
                appTheme.field.tabsTrigger,
                props.class,
            )
        "
    >
        <slot />
    </TabsTrigger>
</template>
