<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import type { RadioGroupItemProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { RadioGroupIndicator, RadioGroupItem } from 'reka-ui'

const props = defineProps<
    RadioGroupItemProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedProps(delegatedProps)
</script>

<template>
    <RadioGroupItem
        data-slot="radio-group-item"
        v-bind="forwarded"
        :class="
            cn(
                appTheme.field.radio,
                props.class,
            )
        "
    >
        <RadioGroupIndicator data-slot="radio-group-indicator" class="relative flex items-center justify-center">
            <IconLucideCircle class="absolute top-1/2 left-1/2 size-2 -translate-x-1/2 -translate-y-1/2 fill-primary" />
        </RadioGroupIndicator>
    </RadioGroupItem>
</template>
