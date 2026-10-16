<script setup lang="ts">
    import type { ButtonVariants } from '@/components/ui/button'

    interface Props {
        open: boolean
        title: string
        description: string
        confirmLabel?: string
        cancelLabel?: string
        confirmVariant?: ButtonVariants['variant']
        processing?: boolean
        showFooter?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        confirmVariant: 'destructive',
        processing: false,
        showFooter: true
    })

    const emit = defineEmits<{
        'update:open': [open: boolean]
        confirm: []
    }>()
</script>

<template>
    <BaseDialog
        :open="props.open"
        :title="props.title"
        :description="props.description"
        :confirm-label="props.confirmLabel"
        :cancel-label="props.cancelLabel"
        :confirm-variant="props.confirmVariant"
        :processing="props.processing"
        :show-footer="props.showFooter"
        @confirm="emit('confirm')"
        @update:open="emit('update:open', $event)"
    >
        <slot />
    </BaseDialog>
</template>
