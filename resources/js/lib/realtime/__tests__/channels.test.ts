import { describe, expect, it } from 'vitest'
import { resolveRealtimeChannel } from '../channels'

describe('resolveRealtimeChannel', () => {
    it('resolves channel patterns with provided parameters', () => {
        expect(
            resolveRealtimeChannel('users.{userId}.notifications', {
                userId: 42
            })
        ).toBe('users.42.notifications')
    })

    it('throws when a required parameter is missing', () => {
        expect(() => resolveRealtimeChannel('users.{userId}.notifications')).toThrow('Missing realtime channel parameter "userId".')
    })
})
