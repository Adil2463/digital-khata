<script setup lang="ts">
    import type { Method } from '@inertiajs/core'

    const theme = appTheme

    interface Props {
        href: string
        tabindex?: number
        method?: Method
        as?: string
    }

    const props = defineProps<Props>()

    const linkProps = computed(() => {
        const resolved: {
            href: string
            tabindex?: number
            method?: Method
            as?: string
        } = {
            href: props.href
        }

        if (props.tabindex !== undefined) {
            resolved.tabindex = props.tabindex
        }

        if (props.method !== undefined) {
            resolved.method = props.method
        }

        if (props.as !== undefined) {
            resolved.as = props.as
        }

        return resolved
    })
</script>

<template>
    <Link v-bind="linkProps" :class="theme.link">
        <slot />
    </Link>
</template>
