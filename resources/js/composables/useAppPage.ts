import type { AppPageProps } from '@/types'
import type { ComputedRef } from 'vue'

type AuthUser = NonNullable<AppPageProps['auth']['user']>

interface UseAuthUserOptions {
    required?: boolean
    context?: string
}

interface RequiredAuthUserOptions extends UseAuthUserOptions {
    required: true
}

export function useAppPage<T extends Record<string, unknown> = Record<string, unknown>>() {
    return usePage<AppPageProps<T>>()
}

export function useAuthUser(options: RequiredAuthUserOptions): ComputedRef<AuthUser>
export function useAuthUser(options?: UseAuthUserOptions): ComputedRef<AuthUser | null>
export function useAuthUser(options: UseAuthUserOptions = {}): ComputedRef<AuthUser | null> {
    const page = useAppPage()

    return computed(() => {
        const user = page.props.auth.user

        if (options.required && user === null) {
            const contextSuffix = options.context !== undefined && options.context !== '' ? ` in ${options.context}` : ''
            throw new Error(`Authenticated user is required${contextSuffix}.`)
        }

        return user
    })
}
