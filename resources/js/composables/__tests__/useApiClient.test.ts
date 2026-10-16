import * as realtimeConfig from '@/lib/realtime/config'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { apiRequest } from '../useApiClient'

const isObjectRecord = (value: unknown): value is Record<string, unknown> => typeof value === 'object' && value !== null

const toJsonResponse = (payload: unknown, status = 200): Response => {
    return new Response(JSON.stringify(payload), {
        status,
        headers: {
            'Content-Type': 'application/json'
        }
    })
}

const toNoContentResponse = (): Response => {
    return new Response(null, {
        status: 204
    })
}

describe('apiRequest', () => {
    afterEach(() => {
        vi.restoreAllMocks()
        vi.unstubAllGlobals()
        document.cookie = 'XSRF-TOKEN=; Max-Age=0; path=/'
    })

    it('reads the current CSRF cookie for mutations, including after session rotation', async () => {
        const fetchMock = vi.fn(async () => toJsonResponse({ ok: true }))
        vi.stubGlobal('fetch', fetchMock)

        for (const token of ['first/token=', 'rotated/token=']) {
            document.cookie = `XSRF-TOKEN=${encodeURIComponent(token)}; path=/`
            await apiRequest({ url: '/app/settings/profile', method: 'PATCH', body: {} })
            expect(fetchMock).toHaveBeenLastCalledWith(
                '/app/settings/profile',
                expect.objectContaining({
                    credentials: 'same-origin',
                    headers: expect.objectContaining({ 'X-XSRF-TOKEN': token })
                })
            )
        }
    })

    it('does not send session or socket headers to another origin', async () => {
        document.cookie = 'XSRF-TOKEN=secret; path=/'
        vi.spyOn(realtimeConfig, 'getRealtimeSocketId').mockReturnValue('private-socket')
        const fetchMock = vi.fn(async () => toJsonResponse({ ok: true }))
        vi.stubGlobal('fetch', fetchMock)

        await apiRequest({ url: 'https://external.example/test', method: 'POST' })
        expect(fetchMock).toHaveBeenCalledWith(
            'https://external.example/test',
            expect.objectContaining({
                headers: { Accept: 'application/json' }
            })
        )
    })

    it('merges query parameters before the fragment without corrupting an existing query', async () => {
        const fetchMock = vi.fn(async () => toJsonResponse({ ok: true }))
        vi.stubGlobal('fetch', fetchMock)

        await apiRequest({ url: '/api/v1/users?page=1&role=admin#results', query: { page: 2, search: 'A & B', ignored: null } })
        expect(fetchMock).toHaveBeenCalledWith('/api/v1/users?page=2&role=admin&search=A+%26+B#results', expect.any(Object))
    })

    it('handles missing and malformed CSRF cookies without breaking requests', async () => {
        const fetchMock = vi.fn(async () => toJsonResponse({ ok: true }))
        vi.stubGlobal('fetch', fetchMock)

        await apiRequest({ url: '/api/v1/ping', method: 'POST' })
        document.cookie = 'XSRF-TOKEN=%invalid; path=/'
        await apiRequest({ url: '/api/v1/ping', method: 'POST' })
        expect(fetchMock).toHaveBeenCalledTimes(2)
    })

    it('returns unknown payload when no parser is provided', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ ok: true }))
        )

        const response = await apiRequest({
            url: '/api/v1/ping'
        })

        expect(response).toEqual({ ok: true })
    })

    it('uses parseResponse to return validated data', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ id: 9, email: 'user@example.com' }))
        )

        const response = await apiRequest({
            url: '/api/v1/users/9',
            parseResponse: (payload) => {
                if (!isObjectRecord(payload) || typeof payload.id !== 'number' || typeof payload.email !== 'string') {
                    throw new Error('Invalid user payload')
                }

                return {
                    id: payload.id,
                    email: payload.email
                }
            }
        })

        expect(response).toEqual({ id: 9, email: 'user@example.com' })
    })

    it('throws a typed error when parseResponse rejects payload', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ id: 'not-a-number' }))
        )

        await expect(
            apiRequest({
                url: '/api/v1/users/invalid',
                parseResponse: () => {
                    throw new Error('Invalid user payload')
                }
            })
        ).rejects.toEqual({
            message: 'Invalid user payload',
            status: 200,
            code: 'invalid_response_payload'
        })
    })

    it('allows parseResponse to handle 204 responses explicitly', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toNoContentResponse())
        )

        const response = await apiRequest({
            url: '/api/v1/users/9',
            method: 'DELETE',
            parseResponse: (payload) => {
                if (payload !== undefined) {
                    throw new Error('Expected no content payload')
                }

                return true
            }
        })

        expect(response).toBe(true)
    })

    it('includes the realtime socket id header when Echo is connected', async () => {
        vi.spyOn(realtimeConfig, 'getRealtimeSocketId').mockReturnValue('socket-123')

        const fetchMock = vi.fn(async (_input: RequestInfo | URL, init?: RequestInit) => {
            expect((init?.headers as Record<string, string>)['X-Socket-ID']).toBe('socket-123')

            return toJsonResponse({ ok: true })
        })

        vi.stubGlobal('fetch', fetchMock)

        await apiRequest({
            url: '/api/v1/ping',
            method: 'POST',
            body: { ok: true }
        })

        expect(fetchMock).toHaveBeenCalledTimes(1)
    })
})
