import { Appearance } from '@/types/app-data'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { useAppearance } from '../useAppearance'

const page = {
    props: {
        appearance: Appearance.Light
    }
}

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => page
}))

describe('useAppearance', () => {
    beforeEach(() => {
        vi.unstubAllEnvs()
        document.documentElement.classList.remove('dark')
        document.cookie = 'appearance=;path=/;max-age=0;SameSite=Lax'
        useAppearance().updateAppearance(Appearance.Light)
    })

    it('updates the document and persists the selected appearance', () => {
        const { appearance, updateAppearance } = useAppearance()

        updateAppearance(Appearance.Dark)

        expect(appearance.value).toBe(Appearance.Dark)
        expect(document.documentElement.classList.contains('dark')).toBe(true)
        expect(document.cookie).toContain('appearance=dark')
    })

    it('does not mutate shared module state while rendering on the server', () => {
        vi.stubEnv('SSR', true)
        const { appearance, updateAppearance } = useAppearance()

        updateAppearance(Appearance.Dark)

        expect(appearance.value).toBe(Appearance.Light)
        expect(document.documentElement.classList.contains('dark')).toBe(false)
        expect(document.cookie).toContain('appearance=light')
    })
})
