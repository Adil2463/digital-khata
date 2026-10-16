<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { HTMLAttributes } from 'vue'
import type { RadioGroupRootEmits, RadioGroupRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { RadioGroupRoot } from 'reka-ui'

const props = defineProps<
    RadioGroupRootProps & {
        class?: HTMLAttributes['class']
    }
>()

const emits = defineEmits<RadioGroupRootEmits>()

const delegatedProps = reactiveOmit(props, 'class')
const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
    <RadioGroupRoot data-slot="radio-group" v-bind="forwarded" :class="cn('grid gap-2', props.class)">
        <slot />
    </RadioGroupRoot>
</template>
