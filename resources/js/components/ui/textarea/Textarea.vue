<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { useVModel } from '@vueuse/core'

const props = defineProps<{
    defaultValue?: string
    modelValue?: string
    class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue ?? '',
})
</script>

<template>
    <textarea
        v-model="modelValue"
        data-slot="textarea"
        :class="
            cn(
                appTheme.field.control,
                appTheme.field.textarea,
                props.class,
            )
        "
    />
</template>
