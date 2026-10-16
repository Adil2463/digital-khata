<script setup lang="ts">
    import type { HTMLAttributes } from 'vue'

    defineOptions({
        inheritAttrs: false
    })

    interface Props {
        className?: HTMLAttributes['class']
    }

    defineProps<Props>()
</script>

<template>
    <Icon-icon-park-outline:system class="size-9 fill-current" :class="className" />
</template>
