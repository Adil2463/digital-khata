import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import { clearApiQueryCache, getApiQueryCacheData, setApiQueryCacheData } from '../useApiQuery'

const echoMocks = vi.hoisted(() => ({
    useEchoMock: vi.fn(),
    useEchoPublicMock: vi.fn(),
    useEchoModelMock: vi.fn(),
    useEchoNotificationMock: vi.fn(),
    useEchoPresenceMock: vi.fn(),
    useConnectionStatusMock: vi.fn(() => ref<'connected' | 'failed'>('connected'))
}))

vi.mock('@laravel/echo-vue', () => ({
    useEcho: echoMocks.useEchoMock,
    useEchoPublic: echoMocks.useEchoPublicMock,
    useEchoModel: echoMocks.useEchoModelMock,
    useEchoNotification: echoMocks.useEchoNotificationMock,
    useEchoPresence: echoMocks.useEchoPresenceMock,
    useConnectionStatus: echoMocks.useConnectionStatusMock
}))

describe('useRealtime composables', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        clearApiQueryCache()
    })

    it('normalizes event names and invalidates cache for private events', async () => {
        let callback: ((payload: { id: number }) => void) | undefined

        echoMocks.useEchoMock.mockImplementation((_channel: string, _event: string | string[], handler: (payload: { id: number }) => void) => {
            callback = handler

            return {
                leaveChannel: vi.fn(),
                leave: vi.fn(),
                stopListening: vi.fn(),
                listen: vi.fn(),
                channel: vi.fn()
            }
        })

        setApiQueryCacheData('users:index', [{ id: 1 }])

        const { useRealtimeEvent } = await import('../useRealtime')

        useRealtimeEvent<{ id: number }>({
            channel: 'users.index',
            event: 'users.list.changed',
            invalidateKeys: ['users:index']
        })

        expect(echoMocks.useEchoMock).toHaveBeenCalledWith('users.index', '.users.list.changed', expect.any(Function), [], 'private')

        callback?.({ id: 9 })

        expect(getApiQueryCacheData('users:index')).toBeUndefined()
    })

    it('tracks presence members and exposes whisper support', async () => {
        let hereHandler: ((members: Array<{ id: number; name: string }>) => void) | undefined
        let joiningHandler: ((member: { id: number; name: string }) => void) | undefined
        let leavingHandler: ((member: { id: number; name: string }) => void) | undefined
        const whisper = vi.fn()

        echoMocks.useEchoPresenceMock.mockImplementation(() => ({
            leaveChannel: vi.fn(),
            leave: vi.fn(),
            stopListening: vi.fn(),
            listen: vi.fn(),
            channel: () => ({
                here(callback: (members: Array<{ id: number; name: string }>) => void) {
                    hereHandler = callback
                    return this
                },
                joining(callback: (member: { id: number; name: string }) => void) {
                    joiningHandler = callback
                    return this
                },
                leaving(callback: (member: { id: number; name: string }) => void) {
                    leavingHandler = callback
                    return this
                },
                whisper
            })
        }))

        const { useRealtimePresence } = await import('../useRealtime')

        const presence = useRealtimePresence<{ id: number; name: string }>({
            channel: 'users.index.presence',
            memberKey: (member) => member.id
        })

        hereHandler?.([{ id: 1, name: 'Alice' }])
        joiningHandler?.({ id: 2, name: 'Bob' })
        leavingHandler?.({ id: 1, name: 'Alice' })
        presence.whisper('cursor.updated', { x: 1, y: 2 })

        expect(presence.members.value).toEqual([{ id: 2, name: 'Bob' }])
        expect(whisper).toHaveBeenCalledWith('cursor.updated', { x: 1, y: 2 })
    })

    it('exposes reactive connection state', async () => {
        const { useRealtimeConnection } = await import('../useRealtimeConnection')

        const connection = useRealtimeConnection()

        expect(connection.status.value).toBe('connected')
        expect(connection.isConnected.value).toBe(true)
        expect(connection.hasFailed.value).toBe(false)
    })
})
