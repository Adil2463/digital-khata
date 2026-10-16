<script setup lang="ts">
    const theme = appTheme

    interface Props {
        id: string
        label: string
        description?: string
        error?: string
        required?: boolean
        hideLabel?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        required: false,
        hideLabel: false
    })
</script>

<template>
    <div class="grid gap-2">
        <UiLabel v-if="!props.hideLabel" :id="`${props.id}-label`" :for="props.id">
            {{ props.label }}
            <span v-if="props.required" class="text-destructive">*</span>
        </UiLabel>
        <slot />
        <p v-if="props.description !== undefined && props.description !== ''" :id="`${props.id}-description`" :class="theme.field.description">
            {{ props.description }}
        </p>
        <InputError :id="`${props.id}-error`" :message="props.error" />
    </div>
</template>
