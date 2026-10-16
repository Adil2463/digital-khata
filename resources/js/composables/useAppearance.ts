import { Appearance } from '@/types/app-data'

const APPEARANCE_COOKIE = 'appearance'
const APPEARANCE_COOKIE_MAX_AGE_SECONDS = 365 * 24 * 60 * 60

// The visitor's own choice, `null` until they actually toggle. Module scope is shared
// by every request the long-lived SSR process renders, so it has to stay null there —
// and it does, because `updateAppearance` only ever runs from a browser interaction.
const clientAppearance = ref<Appearance | null>(null)

export function useAppearance() {
    const page = useAppPage()

    // Seeded from the shared prop rather than from the DOM or localStorage, so the
    // server renders the appearance UI in the state the document is already painted
    // in and the first client render hydrates it without a mismatch. The override
    // takes over from the first toggle, since a client-only change never round-trips
    // through Inertia's shared props.
    const appearance = computed<Appearance>(() => clientAppearance.value ?? page.props.appearance)

    function updateAppearance(value: Appearance): void {
        if (import.meta.env.SSR) {
            return
        }

        clientAppearance.value = value
        document.documentElement.classList.toggle('dark', value === Appearance.Dark)

        // Read back by `HandleAppearance` on the next full page load, which is what
        // lets the server put the class on `<html>` before any CSS is parsed.
        const secureAttribute = window.location.protocol === 'https:' ? ';Secure' : ''
        document.cookie = `${APPEARANCE_COOKIE}=${value};path=/;max-age=${APPEARANCE_COOKIE_MAX_AGE_SECONDS};SameSite=Lax${secureAttribute}`
    }

    return {
        appearance,
        updateAppearance
    }
}
