<script setup lang="ts" generic="TForm extends object">
    import type { FormFieldSchema, FormSectionSchema } from '@/types/base-ui'

    type FormKey = Extract<keyof TForm, string>

    interface Props {
        model: TForm
        fields?: Array<FormFieldSchema<TForm>>
        sections?: Array<FormSectionSchema<TForm>>
        errors?: Partial<Record<FormKey, string>>
        processing?: boolean
        submitLabel?: string
        cancelLabel?: string
        showCancel?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        fields: () => [],
        sections: () => [],
        errors: () => ({}),
        processing: false,
        submitLabel: 'Save',
        cancelLabel: 'Cancel',
        showCancel: false
    })

    const formId = useId()

    const emit = defineEmits<{
        submit: []
        cancel: []
    }>()

    const normalizedSections = computed<Array<FormSectionSchema<TForm>>>(() => {
        if (props.sections.length > 0) {
            return props.sections
        }

        if (props.fields.length > 0) {
            return [
                {
                    key: 'default',
                    fields: props.fields
                }
            ]
        }

        return []
    })

    const getModelFieldValue = (fieldName: FormKey): unknown => {
        return props.model[fieldName]
    }

    const setValue = (fieldName: FormKey, value: unknown): void => {
        ;(props.model as Record<FormKey, unknown>)[fieldName] = value
    }
</script>

<template>
    <form class="space-y-6" @submit.prevent="!props.processing && emit('submit')">
        <div class="space-y-6">
            <section v-for="section in normalizedSections" :key="section.key" class="space-y-4">
                <div v-if="section.title !== undefined || section.description !== undefined" class="space-y-1">
                    <h3 v-if="section.title !== undefined" class="text-base font-semibold">{{ section.title }}</h3>
                    <p v-if="section.description !== undefined" class="text-sm text-muted-foreground">{{ section.description }}</p>
                </div>

                <div class="grid gap-4">
                    <BaseFormsBaseInputField
                        v-for="field in section.fields"
                        :id="`${formId}-${field.name}`"
                        :key="field.name"
                        :field="field"
                        :model-value="getModelFieldValue(field.name)"
                        :error="props.errors[field.name] ?? ''"
                        :disabled="props.processing"
                        @update:model-value="setValue(field.name, $event)"
                    />
                </div>
            </section>
        </div>

        <slot name="actions">
            <BaseFormsBaseFormActions
                :processing="props.processing"
                :submit-label="props.submitLabel"
                :cancel-label="props.cancelLabel"
                :show-cancel="props.showCancel"
                @cancel="emit('cancel')"
            />
        </slot>
    </form>
</template>
