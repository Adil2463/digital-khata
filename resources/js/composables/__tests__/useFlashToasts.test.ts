import type { AppPageProps } from '@/types'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, reactive } from 'vue'
import { useFlashToasts } from '../useFlashToasts'

type ToastInput = {
    title: string
    description?: string
    duration?: number
}

type MockPage = {
    props: Pick<AppPageProps, 'flash'>
}

const { success, error, info, usePageMock, useToastMock } = vi.hoisted(() => {
    const success = vi.fn<(input: ToastInput) => void>()
    const error = vi.fn<(input: ToastInput) => void>()
    const info = vi.fn<(input: ToastInput) => void>()

    return {
        success,
        error,
        info,
        usePageMock: vi.fn(),
        useToastMock: vi.fn(() => ({ success, error, info }))
    }
})

vi.mock('@inertiajs/vue3', () => ({
    usePage: usePageMock
}))

vi.mock('@/composables/useToast', () => ({
    useToast: useToastMock
}))

let mockPage = createMockPage()

beforeEach(() => {
    mockPage = createMockPage()

    usePageMock.mockClear()
    useToastMock.mockClear()
    usePageMock.mockReturnValue(mockPage)
    useToastMock.mockReturnValue({ success, error, info })

    success.mockReset()
    error.mockReset()
    info.mockReset()
})

describe('useFlashToasts', () => {
    it('emits toasts for message, error, and status flash values', async () => {
        useFlashToasts()
        await nextTick()

        expect(success).not.toHaveBeenCalled()
        expect(error).not.toHaveBeenCalled()
        expect(info).not.toHaveBeenCalled()

        mockPage.props.flash.message = 'Profile updated'
        mockPage.props.flash.error = 'Action failed'
        mockPage.props.flash.status = 'Verification sent'
        await nextTick()

        expect(success).toHaveBeenCalledWith({ title: 'Profile updated' })
        expect(error).toHaveBeenCalledWith({ title: 'Action failed' })
        expect(info).toHaveBeenCalledWith({ title: 'Verification sent' })
    })

    it('does not duplicate identical flash values', async () => {
        useFlashToasts()
        await nextTick()

        mockPage.props.flash.message = 'Saved'
        mockPage.props.flash.status = 'Updated'
        await nextTick()

        mockPage.props.flash.message = 'Saved'
        mockPage.props.flash.status = 'Updated'
        await nextTick()

        expect(success).toHaveBeenCalledTimes(1)
        expect(info).toHaveBeenCalledTimes(1)
    })
})

function createMockPage(): MockPage {
    return reactive({
        props: {
            flash: { message: null, error: null, status: null }
        }
    })
}
