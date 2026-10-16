<script setup lang="ts" generic="TForm extends object">
    import type { FormFieldSchema, FormOption } from '@/types/base-ui'

    const theme = appTheme

    interface Props {
        id: string
        field: FormFieldSchema<TForm>
        modelValue: unknown
        error?: string
        disabled?: boolean
    }

    const props = defineProps<Props>()

    const emit = defineEmits<{
        'update:modelValue': [value: unknown]
    }>()

    const resolvedOptions = computed<FormOption[]>(() => props.field.options ?? [])
    const isFieldDisabled = computed(() => props.disabled === true || props.field.disabled === true)
    const isFieldReadonly = computed(() => props.field.readonly === true)
    const isChoiceDisabled = computed(() => isFieldDisabled.value || isFieldReadonly.value)
    const controlAttributes = computed(() => ({
        'aria-invalid': Boolean(props.error),
        'aria-required': props.field.required === true,
        'aria-describedby':
            [props.field.description ? `${props.id}-description` : '', props.error ? `${props.id}-error` : ''].filter(Boolean).join(' ') || undefined
    }))

    const updateValue = (value: unknown): void => {
        if (!isChoiceDisabled.value) emit('update:modelValue', value)
    }

    const onFileChange = (event: Event): void => {
        const target = event.target

        if (!(target instanceof HTMLInputElement)) {
            return
        }

        const files = target.files

        if (files === null) {
            updateValue(props.field.multiple ? [] : null)
            return
        }

        if (props.field.multiple) {
            updateValue(Array.from(files))
            return
        }

        updateValue(files.item(0))
    }

    const onMultiSelectChange = (event: Event): void => {
        const target = event.target

        if (!(target instanceof HTMLSelectElement)) {
            return
        }

        const values = Array.from(target.selectedOptions).map((option) => option.value)
        updateValue(values)
    }

    const toBoolean = (value: unknown): boolean => value === true
</script>

<template>
    <BaseFormsBaseFieldShell
        :id="props.id"
        :label="props.field.label"
        :description="props.field.description ?? ''"
        :error="props.error ?? ''"
        :required="props.field.required === true"
        :hide-label="props.field.type === 'checkbox'"
    >
        <UiInput
            v-if="['text', 'email', 'password'].includes(props.field.type)"
            v-bind="controlAttributes"
            :id="props.id"
            :type="props.field.type"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :autocomplete="props.field.autocomplete"
            :disabled="isFieldDisabled"
            :readonly="isFieldReadonly"
            @update:model-value="updateValue"
        />

        <UiTextarea
            v-else-if="props.field.type === 'textarea'"
            v-bind="controlAttributes"
            :id="props.id"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :disabled="isFieldDisabled"
            :readonly="isFieldReadonly"
            @update:model-value="updateValue"
        />

        <UiSelect
            v-else-if="props.field.type === 'select'"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :disabled="isChoiceDisabled"
            @update:model-value="updateValue"
        >
            <UiSelectTrigger :id="props.id" v-bind="controlAttributes" class="w-full">
                <UiSelectValue :placeholder="props.field.placeholder ?? 'Select an option'" />
            </UiSelectTrigger>
            <UiSelectContent>
                <UiSelectItem v-for="option in resolvedOptions" :key="option.value" :value="option.value" :disabled="option.disabled === true">
                    {{ option.label }}
                </UiSelectItem>
            </UiSelectContent>
        </UiSelect>

        <select
            v-else-if="props.field.type === 'multiselect'"
            v-bind="controlAttributes"
            :id="props.id"
            multiple
            :disabled="isChoiceDisabled"
            :class="[theme.field.control, theme.field.multiselect]"
            @change="onMultiSelectChange"
        >
            <option
                v-for="option in resolvedOptions"
                :key="option.value"
                :value="option.value"
                :disabled="option.disabled === true"
                :selected="Array.isArray(props.modelValue) && props.modelValue.includes(option.value)"
            >
                {{ option.label }}
            </option>
        </select>

        <UiInput
            v-else-if="props.field.type === 'file'"
            v-bind="controlAttributes"
            :id="props.id"
            type="file"
            :accept="props.field.accept"
            :multiple="props.field.multiple"
            :disabled="isChoiceDisabled"
            @change="onFileChange"
        />

        <div v-else-if="props.field.type === 'checkbox'" class="flex items-center gap-2">
            <UiCheckbox
                :id="props.id"
                v-bind="controlAttributes"
                :model-value="toBoolean(props.modelValue)"
                :disabled="isChoiceDisabled"
                @update:model-value="updateValue($event === true)"
            />
            <UiLabel :id="`${props.id}-label`" :for="props.id">
                {{ props.field.placeholder ?? props.field.label }}
                <span v-if="props.field.required" class="text-destructive">*</span>
            </UiLabel>
        </div>

        <div v-else-if="props.field.type === 'toggle'" :class="theme.field.toggle">
            <div class="space-y-0.5">
                <p class="text-sm font-medium">{{ props.field.placeholder ?? props.field.label }}</p>
            </div>
            <UiSwitch
                :id="props.id"
                v-bind="controlAttributes"
                :model-value="toBoolean(props.modelValue)"
                :disabled="isChoiceDisabled"
                @update:model-value="updateValue($event === true)"
            />
        </div>

        <UiRadioGroup
            v-else-if="props.field.type === 'radio'"
            :id="props.id"
            v-bind="controlAttributes"
            :aria-labelledby="`${props.id}-label`"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :disabled="isChoiceDisabled"
            class="space-y-2"
            @update:model-value="updateValue"
        >
            <div v-for="option in resolvedOptions" :key="option.value" class="flex items-center gap-2">
                <UiRadioGroupItem :id="`${props.id}-${option.value}`" :value="option.value" :disabled="option.disabled === true" />
                <UiLabel :for="`${props.id}-${option.value}`">{{ option.label }}</UiLabel>
            </div>
        </UiRadioGroup>

        <UiTabs
            v-else-if="props.field.type === 'tabs'"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            @update:model-value="updateValue"
        >
            <UiTabsList :id="props.id" v-bind="controlAttributes" :aria-labelledby="`${props.id}-label`" class="w-full">
                <UiTabsTrigger
                    v-for="option in resolvedOptions"
                    :key="option.value"
                    :value="option.value"
                    :disabled="isChoiceDisabled || option.disabled === true"
                    class="flex-1"
                >
                    {{ option.label }}
                </UiTabsTrigger>
            </UiTabsList>
        </UiTabs>

        <UiInput
            v-else
            v-bind="controlAttributes"
            :id="props.id"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :disabled="isFieldDisabled"
            :readonly="isFieldReadonly"
            @update:model-value="updateValue"
        />
    </BaseFormsBaseFieldShell>
</template>
