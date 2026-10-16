<script setup lang="ts">
import { useForwardedProps } from '@/lib/forward-props'
import { DropdownMenuTrigger, type DropdownMenuTriggerProps } from 'reka-ui'

const props = defineProps<DropdownMenuTriggerProps>()

const forwardedProps = useForwardedProps(props)
</script>

<template>
    <DropdownMenuTrigger data-slot="dropdown-menu-trigger" v-bind="forwardedProps">
        <slot />
    </DropdownMenuTrigger>
</template>
