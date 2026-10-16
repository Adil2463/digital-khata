import type { ToastMessage, ToastVariant } from '@/types/base-ui'

interface CreateToastInput {
    title: string
    description?: string
    duration?: number
}

const DEFAULT_DURATION = 4000
const toasts = ref<ToastMessage[]>([])
const timeoutIds = new Map<string, ReturnType<typeof setTimeout>>()
let toastCounter = 0

const normalizeDuration = (duration?: number): number => {
    if (typeof duration !== 'number' || Number.isNaN(duration)) {
        return DEFAULT_DURATION
    }

    return Math.max(1000, duration)
}

const buildToast = (variant: ToastVariant, input: CreateToastInput): ToastMessage => {
    const toast: ToastMessage = {
        id: `toast-${Date.now()}-${(toastCounter += 1)}`,
        title: input.title,
        variant,
        duration: normalizeDuration(input.duration)
    }

    if (input.description !== undefined) {
        toast.description = input.description
    }

    return toast
}

const scheduleDismiss = (id: string, duration: number): void => {
    const timeoutId = setTimeout(() => dismiss(id), duration)
    timeoutIds.set(id, timeoutId)
}

const create = (variant: ToastVariant, input: CreateToastInput): void => {
    // Toasts are transient client-side UI. Dropping them on the server is what
    // keeps this module-level state and its timers empty in the long-lived SSR
    // process, so nothing can leak between requests.
    if (import.meta.env.SSR) {
        return
    }

    const toast = buildToast(variant, input)
    toasts.value = [toast, ...toasts.value]
    scheduleDismiss(toast.id, toast.duration)
}

const dismiss = (id: string): void => {
    const timeoutId = timeoutIds.get(id)

    if (timeoutId !== undefined) {
        clearTimeout(timeoutId)
        timeoutIds.delete(id)
    }

    toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

export function useToast() {
    const success = (input: CreateToastInput): void => create('success', input)
    const error = (input: CreateToastInput): void => create('error', input)
    const info = (input: CreateToastInput): void => create('info', input)
    const warning = (input: CreateToastInput): void => create('warning', input)
    const basic = (input: CreateToastInput): void => create('default', input)
    const clear = (): void => {
        timeoutIds.forEach((timeoutId) => clearTimeout(timeoutId))
        timeoutIds.clear()
        toasts.value = []
    }

    return {
        toasts: readonly(toasts),
        toast: basic,
        success,
        error,
        info,
        warning,
        dismiss,
        clear
    }
}
