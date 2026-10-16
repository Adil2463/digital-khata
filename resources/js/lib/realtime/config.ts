import { configureEcho, echo, echoIsConfigured } from '@laravel/echo-vue'
import type { EchoOptions } from 'laravel-echo'

export type RealtimeAuthMode = 'session' | 'bearer'

export interface RealtimeAuthStrategy {
    mode?: RealtimeAuthMode
    bearerToken?: string
    headers?: Record<string, string>
}

export interface ConfigureRealtimeOptions {
    auth?: RealtimeAuthStrategy
    overrides?: Partial<EchoOptions<'reverb'>>
}

const parsePort = (value: string | undefined, fallback: number): number => {
    if (value === undefined || value.trim() === '') {
        return fallback
    }

    const parsed = Number(value)

    return Number.isInteger(parsed) && parsed > 0 ? parsed : fallback
}

const resolveRealtimeScheme = (): 'http' | 'https' => {
    return import.meta.env.VITE_REVERB_SCHEME === 'https' ? 'https' : 'http'
}

const resolveAuthEndpoints = (
    mode: RealtimeAuthMode
): { authEndpoint: string; userAuthentication: { endpoint: string; headers: Record<string, string> } } => {
    if (mode === 'bearer') {
        return {
            authEndpoint: '/api/broadcasting/auth',
            userAuthentication: {
                endpoint: '/api/broadcasting/user-auth',
                headers: {}
            }
        }
    }

    return {
        authEndpoint: '/broadcasting/auth',
        userAuthentication: {
            endpoint: '/broadcasting/user-auth',
            headers: {}
        }
    }
}

export const createRealtimeConfig = (options: ConfigureRealtimeOptions = {}): EchoOptions<'reverb'> => {
    const scheme = resolveRealtimeScheme()
    const mode = options.auth?.mode ?? 'session'
    const authHeaders: Record<string, string> = {
        ...(options.auth?.headers ?? {})
    }
    const endpoints = resolveAuthEndpoints(mode)

    if (mode === 'bearer' && options.auth?.bearerToken !== undefined && options.auth.bearerToken !== '') {
        authHeaders.Authorization = `Bearer ${options.auth.bearerToken}`
        endpoints.userAuthentication.headers.Authorization = authHeaders.Authorization
    }

    if (mode === 'session') {
        endpoints.userAuthentication.headers = {
            ...(options.auth?.headers ?? {})
        }
    }

    const baseConfig: EchoOptions<'reverb'> = {
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || (typeof window !== 'undefined' ? window.location.hostname : '127.0.0.1'),
        wsPort: parsePort(import.meta.env.VITE_REVERB_PORT, 8080),
        wssPort: parsePort(import.meta.env.VITE_REVERB_PORT, 443),
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        namespace: false,
        withoutInterceptors: true,
        auth: {
            headers: authHeaders
        },
        authEndpoint: endpoints.authEndpoint,
        userAuthentication: {
            ...endpoints.userAuthentication,
            transport: 'ajax'
        }
    }

    return {
        ...baseConfig,
        ...(options.overrides ?? {})
    }
}

/**
 * Configures the single Echo instance used by every realtime composable.
 *
 * Both app entries call this. Under SSR the `null` broadcaster is used so
 * realtime composables resolve to inert channels instead of throwing or
 * opening a WebSocket while the server renders a page.
 */
export const configureRealtime = (options: ConfigureRealtimeOptions = {}): void => {
    if (import.meta.env.SSR) {
        configureEcho<'null'>({ broadcaster: 'null' })

        return
    }

    configureEcho(createRealtimeConfig(options))
}

export const getRealtimeClient = () => echo<'reverb'>()

export const isRealtimeConfigured = (): boolean => echoIsConfigured()

export const getRealtimeSocketId = (): string | undefined => {
    if (!isRealtimeConfigured()) {
        return undefined
    }

    try {
        return getRealtimeClient().socketId()
    } catch {
        return undefined
    }
}

export const disconnectRealtime = (): void => {
    if (!isRealtimeConfigured()) {
        return
    }

    getRealtimeClient().disconnect()
}
