<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { NavigationMenuTrigger, type NavigationMenuTriggerProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'
import { navigationMenuTriggerStyles } from '.'

const props = defineProps<NavigationMenuTriggerProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')

const forwardedProps = useForwardedProps(delegatedProps)
</script>

<template>
    <NavigationMenuTrigger data-slot="navigation-menu-trigger" v-bind="forwardedProps"
        :class="cn(navigationMenuTriggerStyles, props.class)">
        <slot />
        <IconLucideChevronDown class="relative top-[1px] ml-1 size-3 transition duration-300 group-data-[state=open]:rotate-180"
            aria-hidden="true" />
    </NavigationMenuTrigger>
</template>
