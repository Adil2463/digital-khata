import { router } from '@inertiajs/vue3'
import { describe, expect, it, vi } from 'vitest'
import { effectScope, nextTick, ref } from 'vue'
import { resolveServerTableInitialQuery, useServerDataTable } from '../useServerDataTable'

it('synchronizes pagination after a preserved-state mutation redirect without issuing another visit', () => {
    const visit = vi.spyOn(router, 'get').mockImplementation(() => undefined)
    const pagination = ref({ current_page: 2, per_page: 5 })
    const scope = effectScope()

    try {
        scope.run(() => {
            const table = useServerDataTable({
                endpoint: ({ query } = {}) => ({ url: `/records?page=${query?.page}&perPage=${query?.perPage}`, method: 'get' }),
                initialQuery: () => ({ page: pagination.value.current_page, perPage: pagination.value.per_page })
            })

            pagination.value = { current_page: 1, per_page: 15 }
            expect(table.query.value).toEqual({ page: 1, perPage: 15 })
            expect(visit).not.toHaveBeenCalled()

            table.setPage(2)
            expect(visit).toHaveBeenCalledExactlyOnceWith(
                '/records?page=2&perPage=15',
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true
                }
            )
        })
    } finally {
        scope.stop()
        visit.mockRestore()
    }
})

type SortColumn = 'name' | 'email' | 'role' | 'created_at'

it('synchronizes filters after redirects and cancels obsolete pending searches', async () => {
    vi.useFakeTimers()
    const visit = vi.spyOn(router, 'get').mockImplementation(() => undefined)
    const serverQuery = ref({ page: 2, perPage: 5, search: 'alice', sortBy: 'name', sortDirection: 'asc' as 'asc' | 'desc' })
    const scope = effectScope()
    try {
        const table = scope.run(() =>
            useServerDataTable({
                endpoint: ({ query } = {}) => ({
                    url: `/records?${new URLSearchParams(Object.entries(query ?? {}).map(([key, value]) => [key, String(value)]))}`,
                    method: 'get'
                }),
                initialQuery: serverQuery
            })
        )
        if (table === undefined) throw new Error('Expected an active table scope.')
        table.searchValue.value = 'pending search'
        await nextTick()
        serverQuery.value = { page: 1, perPage: 15, search: '', sortBy: 'created_at', sortDirection: 'desc' }
        expect(table.query.value).toEqual(serverQuery.value)
        expect(table.searchValue.value).toBe('')
        await nextTick()
        await vi.runAllTimersAsync()
        expect(visit).not.toHaveBeenCalled()
        table.setPage(2)
        expect(visit.mock.calls[0]?.[0]).toBe('/records?page=2&perPage=15&sortBy=created_at&sortDirection=desc')
        table.searchValue.value = 'bob'
        await nextTick()
        await vi.runAllTimersAsync()
        expect(visit).toHaveBeenCalledTimes(2)
        expect(table.query.value.search).toBe('bob')
        table.searchValue.value = 'unmounted search'
        await nextTick()
        scope.stop()
        await vi.runAllTimersAsync()
        expect(visit).toHaveBeenCalledTimes(2)
    } finally {
        scope.stop()
        visit.mockRestore()
        vi.useRealTimers()
    }
})

describe('resolveServerTableInitialQuery', () => {
    const fallback = {
        page: 2,
        perPage: 25,
        sortBy: 'created_at' as SortColumn,
        sortDirection: 'desc' as const
    }

    it('returns fallback values when location search is empty', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at']
        })

        expect(result).toEqual(fallback)
    })

    it('parses valid query values from location search', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '?page=3&perPage=50&search=alice&sortBy=name&sortDirection=asc',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at']
        })

        expect(result).toEqual({
            page: 3,
            perPage: 50,
            search: 'alice',
            sortBy: 'name',
            sortDirection: 'asc'
        })
    })

    it('falls back for invalid or disallowed query values', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '?page=0&perPage=-1&search=   &sortBy=id&sortDirection=down',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at'],
            defaultSortBy: 'created_at',
            defaultSortDirection: 'desc'
        })

        expect(result).toEqual({
            page: 2,
            perPage: 25,
            sortBy: 'created_at',
            sortDirection: 'desc'
        })
    })
})
