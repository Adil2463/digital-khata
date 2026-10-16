<script setup lang="ts">
    import type { ButtonVariants } from '@/components/ui/button'

    const theme = appTheme

    interface Props {
        processing?: boolean
        submitLabel?: string
        cancelLabel?: string
        showCancel?: boolean
        submitVariant?: ButtonVariants['variant']
    }

    const props = withDefaults(defineProps<Props>(), {
        processing: false,
        submitLabel: 'Save',
        cancelLabel: 'Cancel',
        showCancel: false,
        submitVariant: 'default'
    })

    const emit = defineEmits<{
        cancel: []
    }>()
</script>

<template>
    <div :class="theme.form.actions">
        <BaseButton
            v-if="props.showCancel"
            type="button"
            variant="ghost"
            :disabled="props.processing"
            :label="props.cancelLabel"
            @click="emit('cancel')"
        />
        <BaseButton type="submit" :variant="props.submitVariant" :loading="props.processing" :label="props.submitLabel" />
    </div>
</template>
