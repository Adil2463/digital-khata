import { describe, expect, it } from 'vitest'
import { createRealtimeConfig } from '../config'

describe('createRealtimeConfig', () => {
    it('builds session-based Reverb config from Vite env', () => {
        Object.assign(import.meta.env, {
            VITE_REVERB_APP_KEY: 'test-key',
            VITE_REVERB_HOST: '127.0.0.1',
            VITE_REVERB_PORT: '8080',
            VITE_REVERB_SCHEME: 'http'
        })

        const config = createRealtimeConfig()

        expect(config.broadcaster).toBe('reverb')
        expect(config.key).toBe('test-key')
        expect(config.authEndpoint).toBe('/broadcasting/auth')
        expect(config.userAuthentication!.endpoint).toBe('/broadcasting/user-auth')
        expect(config.forceTLS).toBe(false)
        expect(config.namespace).toBe(false)
    })

    it('builds bearer-token auth endpoints for Sanctum clients', () => {
        Object.assign(import.meta.env, {
            VITE_REVERB_APP_KEY: 'test-key',
            VITE_REVERB_HOST: '127.0.0.1',
            VITE_REVERB_PORT: '8080',
            VITE_REVERB_SCHEME: 'https'
        })

        const config = createRealtimeConfig({
            auth: {
                mode: 'bearer',
                bearerToken: 'token-123'
            }
        })

        expect(config.authEndpoint).toBe('/api/broadcasting/auth')
        expect(config.userAuthentication!.endpoint).toBe('/api/broadcasting/user-auth')
        expect(config.auth?.headers.Authorization).toBe('Bearer token-123')
        expect(config.forceTLS).toBe(true)
    })
})
