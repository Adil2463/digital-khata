<script setup lang="ts">
    import type { ButtonVariants } from '@/components/ui/button'

    interface Props {
        open: boolean
        title: string
        description?: string
        confirmLabel?: string
        cancelLabel?: string
        confirmVariant?: ButtonVariants['variant']
        processing?: boolean
        showFooter?: boolean
        showCancel?: boolean
        maxWidthClass?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        description: '',
        confirmLabel: 'Save',
        cancelLabel: 'Cancel',
        confirmVariant: 'default',
        processing: false,
        showFooter: true,
        showCancel: true,
        maxWidthClass: 'sm:max-w-md'
    })

    const emit = defineEmits<{
        'update:open': [open: boolean]
        confirm: []
        cancel: []
    }>()

    const close = () => {
        emit('cancel')
        emit('update:open', false)
    }
</script>

<template>
    <UiDialog :open="props.open" @update:open="emit('update:open', $event)">
        <UiDialogContent :class="['max-h-[calc(100svh-2rem)] overflow-y-auto', props.maxWidthClass]">
            <UiDialogHeader>
                <UiDialogTitle>{{ props.title }}</UiDialogTitle>
                <UiDialogDescription v-if="props.description !== ''">{{ props.description }}</UiDialogDescription>
            </UiDialogHeader>

            <slot />

            <UiDialogFooter v-if="props.showFooter">
                <BaseButton
                    v-if="props.showCancel"
                    type="button"
                    variant="ghost"
                    class="w-full sm:w-auto"
                    :disabled="props.processing"
                    :label="props.cancelLabel"
                    @click="close"
                />
                <slot name="footer">
                    <BaseButton
                        type="button"
                        class="w-full sm:w-auto"
                        :variant="props.confirmVariant"
                        :loading="props.processing"
                        :label="props.confirmLabel"
                        @click="emit('confirm')"
                    />
                </slot>
            </UiDialogFooter>
        </UiDialogContent>
    </UiDialog>
</template>
