<script setup lang="ts">
    import type { ButtonVariants } from '@/components/ui/button'
    import type { Component, HTMLAttributes } from 'vue'

    interface Props {
        label?: string
        variant?: ButtonVariants['variant']
        size?: ButtonVariants['size']
        type?: 'button' | 'submit' | 'reset'
        loading?: boolean
        loadingText?: string
        disabled?: boolean
        fullWidth?: boolean
        iconLeft?: Component
        iconRight?: Component
        class?: HTMLAttributes['class']
    }

    const props = withDefaults(defineProps<Props>(), {
        variant: 'default',
        size: 'default',
        type: 'button',
        loading: false,
        loadingText: 'Processing...',
        disabled: false,
        fullWidth: false
    })

    const isDisabled = computed(() => props.disabled || props.loading)
    const buttonClass = computed(() => cn(props.fullWidth && 'w-full', props.class))
</script>

<template>
    <UiButton :type="type" :variant="variant" :size="size" :disabled="isDisabled" :class="buttonClass">
        <IconLucideLoaderCircle v-if="loading" class="size-4 animate-spin" />
        <component v-else-if="iconLeft !== undefined" :is="iconLeft" class="size-4" />
        <span v-if="label !== undefined">
            {{ loading ? loadingText : label }}
        </span>
        <slot v-else />
        <component v-if="!loading && iconRight !== undefined" :is="iconRight" class="size-4" />
    </UiButton>
</template>
