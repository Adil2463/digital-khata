<script setup lang="ts">
    const theme = appTheme

    defineProps<{
        id?: string | undefined
        message?: string | undefined
    }>()
</script>

<template>
    <div :id="id" v-show="message">
        <p :class="theme.field.error">
            {{ message }}
        </p>
    </div>
</template>
