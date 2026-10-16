<script setup lang="ts">
    const props = defineProps<{
        title?: string
        description?: string
    }>()

    const layoutProps = computed(() => {
        const resolved: {
            title?: string
            description?: string
        } = {}

        if (props.title !== undefined) {
            resolved.title = props.title
        }

        if (props.description !== undefined) {
            resolved.description = props.description
        }

        return resolved
    })
</script>

<template>
    <AuthSimpleLayout v-bind="layoutProps">
        <slot />
    </AuthSimpleLayout>
</template>
