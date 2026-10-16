import { beforeEach, describe, expect, it, vi } from 'vitest'
import { effectScope, nextTick, ref } from 'vue'
import { clearApiQueryCache, getApiQueryCacheData, invalidateApiQueryCache, setApiQueryCacheData, useApiMutation, useApiQuery } from '../useApiQuery'

// A macrotask boundary drains every pending microtask, so an assertion can observe
// the end of a fetch chain without counting the individual `await` hops inside it.
const flushPromises = async (): Promise<void> => {
    await new Promise((resolve) => {
        setTimeout(resolve, 0)
    })
}

describe('useApiQuery', () => {
    beforeEach(() => {
        clearApiQueryCache()
    })

    it('clears mounted query state synchronously when the account cache is cleared', async () => {
        const scope = effectScope()
        const pending = Promise.withResolvers<string>()
        const queryFn = vi
            .fn<() => Promise<string>>()
            .mockResolvedValueOnce('private account data')
            .mockImplementationOnce(() => pending.promise)
        const query = scope.run(() => useApiQuery({ key: 'account-state', queryFn, enabled: false }))!
        try {
            await query.refresh()
            const request = query.refresh()
            clearApiQueryCache()

            expect(query.data.value).toBeUndefined()
            expect(query.error.value).toBeNull()
            expect(query.isFetching.value).toBe(false)
            pending.resolve('stale account data')
            await request
            expect(query.data.value).toBeUndefined()
        } finally {
            scope.stop()
        }
    })

    it('does not expose previous-key data while the next key is loading', async () => {
        const scope = effectScope()
        const key = ref('first-record')
        const pending = Promise.withResolvers<string>()
        const queryFn = vi
            .fn<() => Promise<string>>()
            .mockResolvedValueOnce('first record')
            .mockImplementationOnce(() => pending.promise)
        const query = scope.run(() => useApiQuery({ key, queryFn }))!
        try {
            await query.refresh()
            key.value = 'second-record'
            await nextTick()
            expect(query.data.value).toBeUndefined()
            expect(query.isLoading.value).toBe(true)
            pending.resolve('second record')
            await query.refresh()
            expect(query.data.value).toBe('second record')
        } finally {
            scope.stop()
        }
    })

    it('batches reactive key changes without fetching intermediate keys', async () => {
        const scope = effectScope()
        const key = ref('first')
        const queryFn = vi.fn(async () => key.value)
        scope.run(() => useApiQuery({ key, queryFn }))
        try {
            await flushPromises()
            key.value = 'intermediate'
            key.value = 'final'
            await flushPromises()
            expect(queryFn).toHaveBeenCalledTimes(2)
            expect(getApiQueryCacheData('intermediate')).toBeUndefined()
            expect(getApiQueryCacheData('final')).toBe('final')
        } finally {
            scope.stop()
        }
    })

    it('keeps string keys distinct from structured keys in cache and in-flight requests', async () => {
        const stringKey = '["users"]'
        const arrayKey = ['users']
        const first = useApiQuery({ key: stringKey, queryFn: async () => 'string-result', enabled: false })
        const second = useApiQuery({ key: arrayKey, queryFn: async () => 'array-result', enabled: false })

        await Promise.all([first.refresh(), second.refresh()])

        expect(first.data.value).toBe('string-result')
        expect(second.data.value).toBe('array-result')
        expect(getApiQueryCacheData(stringKey)).toBe('string-result')
        expect(getApiQueryCacheData(arrayKey)).toBe('array-result')
        invalidateApiQueryCache(stringKey)
        expect(getApiQueryCacheData(arrayKey)).toBe('array-result')
    })

    it.each(['key-change', 'cache-clear'] as const)('stops retrying a request superseded by %s', async (change) => {
        vi.useFakeTimers()
        try {
            const key = ref('first-account')
            const queryFn = vi.fn(async () => {
                if (queryFn.mock.calls.length === 1) throw new Error('temporary failure')
                return key.value
            })
            const query = useApiQuery({ key, queryFn, enabled: false, retryDelayMs: 100 })
            const request = query.refresh().catch(() => undefined)
            await vi.advanceTimersByTimeAsync(0)

            if (change === 'key-change') key.value = 'second-account'
            else clearApiQueryCache()

            await vi.advanceTimersByTimeAsync(100)
            await request

            expect(queryFn).toHaveBeenCalledTimes(1)
            expect(getApiQueryCacheData('first-account')).toBeUndefined()
        } finally {
            vi.useRealTimers()
        }
    })

    it('reuses cached data for the same cache key within stale time', async () => {
        const queryFn = vi.fn(async () => ({ count: 1 }))

        const firstQuery = useApiQuery({
            key: ['users', 'summary'],
            queryFn,
            enabled: false,
            staleTimeMs: 60_000
        })

        await firstQuery.refresh()

        const secondQuery = useApiQuery({
            key: ['users', 'summary'],
            queryFn,
            staleTimeMs: 60_000
        })

        await nextTick()

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(secondQuery.data.value).toEqual({ count: 1 })
    })

    it('never fetches or caches while the server renders', async () => {
        vi.stubEnv('SSR', true)

        try {
            const queryFn = vi.fn(async () => ({ count: 1 }))

            const query = useApiQuery({
                key: 'ssr-query',
                queryFn
            })

            await nextTick()
            await query.refresh()

            expect(queryFn).not.toHaveBeenCalled()
            expect(query.isLoading.value).toBe(false)

            // The SSR process is shared by every visitor, so nothing may be written
            // to (or read back from) the module-level cache while it renders.
            setApiQueryCacheData<number[]>('ssr-query', [1])
            expect(getApiQueryCacheData<number[]>('ssr-query')).toBeUndefined()
        } finally {
            vi.unstubAllEnvs()
        }

        expect(getApiQueryCacheData<number[]>('ssr-query')).toBeUndefined()
    })

    it('shares one in-flight request between consumers of the same key', async () => {
        let resolveQuery: (value: { count: number }) => void = () => undefined
        const queryFn = vi.fn(
            async () =>
                await new Promise<{ count: number }>((resolve) => {
                    resolveQuery = resolve
                })
        )

        const firstQuery = useApiQuery({ key: 'shared-key', queryFn })
        // `select` stays per consumer, so joining the shared request must not leak
        // one consumer's projection into the other.
        const secondQuery = useApiQuery({ key: 'shared-key', queryFn, select: (value) => value.count })

        await nextTick()
        expect(queryFn).toHaveBeenCalledTimes(1)

        resolveQuery({ count: 7 })
        await Promise.all([firstQuery.refresh(), secondQuery.refresh()])

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(firstQuery.data.value).toEqual({ count: 7 })
        expect(secondQuery.data.value).toBe(7)
        expect(getApiQueryCacheData<{ count: number }>('shared-key')).toEqual({ count: 7 })
    })

    it('does not let an invalidated request overwrite a newer response', async () => {
        let resolveFirstRequest: (value: { count: number }) => void = () => undefined
        const queryFn = vi
            .fn<() => Promise<{ count: number }>>()
            .mockImplementationOnce(
                async () =>
                    await new Promise<{ count: number }>((resolve) => {
                        resolveFirstRequest = resolve
                    })
            )
            .mockResolvedValueOnce({ count: 2 })

        const query = useApiQuery({
            key: 'versioned-key',
            queryFn,
            enabled: false
        })

        const firstRequest = query.refresh()
        invalidateApiQueryCache('versioned-key')
        const secondRequest = query.refresh()

        await expect(secondRequest).resolves.toEqual({ count: 2 })

        resolveFirstRequest({ count: 1 })
        await expect(firstRequest).resolves.toEqual({ count: 2 })

        expect(queryFn).toHaveBeenCalledTimes(2)
        expect(query.data.value).toEqual({ count: 2 })
        expect(getApiQueryCacheData<{ count: number }>('versioned-key')).toEqual({ count: 2 })
    })

    it('does not let an in-flight request overwrite an optimistic cache update', async () => {
        let resolveRequest: (value: number[]) => void = () => undefined
        const queryFn = vi.fn(
            async () =>
                await new Promise<number[]>((resolve) => {
                    resolveRequest = resolve
                })
        )

        const query = useApiQuery({
            key: 'optimistic-key',
            queryFn,
            enabled: false
        })

        const request = query.refresh()
        setApiQueryCacheData('optimistic-key', [2])
        resolveRequest([1])

        await expect(request).resolves.toBeUndefined()
        expect(getApiQueryCacheData<number[]>('optimistic-key')).toEqual([2])
    })

    it('does not let a request for an old reactive key overwrite the current query state', async () => {
        let resolveFirstRequest: (value: { count: number }) => void = () => undefined
        const key = ref('first-key')
        const queryFn = vi
            .fn<() => Promise<{ count: number }>>()
            .mockImplementationOnce(
                async () =>
                    await new Promise<{ count: number }>((resolve) => {
                        resolveFirstRequest = resolve
                    })
            )
            .mockResolvedValueOnce({ count: 2 })

        const query = useApiQuery({
            key,
            queryFn,
            enabled: false
        })

        const firstRequest = query.refresh()
        key.value = 'second-key'
        await expect(query.refresh()).resolves.toEqual({ count: 2 })

        resolveFirstRequest({ count: 1 })
        await expect(firstRequest).resolves.toEqual({ count: 1 })

        expect(query.data.value).toEqual({ count: 2 })
        expect(getApiQueryCacheData<{ count: number }>('first-key')).toEqual({ count: 1 })
        expect(getApiQueryCacheData<{ count: number }>('second-key')).toEqual({ count: 2 })
    })

    it('requires a selector when the result type differs from the fetched data', () => {
        const queryFn = async (): Promise<{ count: number }> => ({ count: 1 })

        // @ts-expect-error A projected result requires an explicit selector.
        useApiQuery<{ count: number }, string>({
            key: 'invalid-projection',
            queryFn,
            enabled: false
        })
    })

    it('does not report loading for a disabled query without initial data', async () => {
        const enabled = ref(false)
        const queryFn = vi.fn(async () => ({ count: 1 }))

        const query = useApiQuery({ key: 'disabled-key', queryFn, enabled })

        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(false)
        expect(queryFn).not.toHaveBeenCalled()

        enabled.value = true
        await nextTick()
        await flushPromises()

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(true)
        expect(query.data.value).toEqual({ count: 1 })
    })

    it('does not let an in-flight request update query state after the query is disabled', async () => {
        let resolveRequest: (value: { count: number }) => void = () => undefined
        const enabled = ref(true)
        const queryFn = vi.fn(
            async () =>
                await new Promise<{ count: number }>((resolve) => {
                    resolveRequest = resolve
                })
        )

        const query = useApiQuery({ key: 'disabled-in-flight', queryFn, enabled })
        await nextTick()

        enabled.value = false
        await nextTick()
        resolveRequest({ count: 1 })
        await flushPromises()

        expect(query.data.value).toBeUndefined()
        expect(query.isLoading.value).toBe(false)
        expect(query.isFetching.value).toBe(false)
        expect(getApiQueryCacheData<{ count: number }>('disabled-in-flight')).toEqual({ count: 1 })
    })

    it('captures a failed background fetch in `error` without rejecting the watcher', async () => {
        const queryFn = vi.fn(async () => {
            throw new Error('network down')
        })

        const query = useApiQuery({ key: 'failing-key', queryFn, retry: 0 })

        await flushPromises()

        expect(query.isError.value).toBe(true)
        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(false)
    })

    it('retries failed requests and resolves when a retry succeeds', async () => {
        const queryFn = vi
            .fn<() => Promise<{ ok: boolean }>>()
            .mockRejectedValueOnce(new Error('network 1'))
            .mockRejectedValueOnce(new Error('network 2'))
            .mockResolvedValue({ ok: true })

        const query = useApiQuery({
            key: 'retry-query',
            queryFn,
            enabled: false,
            retry: 2,
            retryDelayMs: 0
        })

        await expect(query.refresh()).resolves.toEqual({ ok: true })
        expect(queryFn).toHaveBeenCalledTimes(3)
        expect(query.error.value).toBeNull()
    })
})

describe('useApiMutation', () => {
    beforeEach(() => {
        clearApiQueryCache()
    })

    it.each(['success', 'failure'] as const)('does not run old-account callbacks after a late mutation %s', async (outcome) => {
        const pending = Promise.withResolvers<number>()
        const onSuccess = vi.fn()
        const onError = vi.fn(() => {
            setApiQueryCacheData('account', 'old account')
        })
        const onSettled = vi.fn()
        const mutation = useApiMutation({ mutationFn: () => pending.promise, onSuccess, onError, onSettled, invalidateKeys: ['account'] })
        const result = mutation.mutate(undefined)
        await nextTick()
        clearApiQueryCache()
        setApiQueryCacheData('account', 'new account')
        const assertion = expect(result).rejects.toMatchObject({ code: 'stale_auth_context' })
        if (outcome === 'success') pending.resolve(1)
        else pending.reject(new Error('Old account write failed'))
        await assertion

        expect(mutation.data.value).toBeUndefined()
        expect(mutation.error.value).toBeNull()
        expect(mutation.isPending.value).toBe(false)
        expect(onSuccess).not.toHaveBeenCalled()
        expect(onError).not.toHaveBeenCalled()
        expect(onSettled).not.toHaveBeenCalled()
        expect(getApiQueryCacheData('account')).toBe('new account')
    })

    it('does not start a write if the account changes during optimistic preparation', async () => {
        const preparation = Promise.withResolvers<void>()
        const mutationFn = vi.fn(async () => 1)
        const mutation = useApiMutation({ mutationFn, onMutate: () => preparation.promise })
        const result = mutation.mutate(undefined)
        clearApiQueryCache()
        const assertion = expect(result).rejects.toMatchObject({ code: 'stale_auth_context' })
        preparation.resolve()
        await assertion
        expect(mutationFn).not.toHaveBeenCalled()
    })

    it.each(['success', 'failure'] as const)('skips settlement if the account changes during a %s callback', async (outcome) => {
        const callback = Promise.withResolvers<void>()
        const callbackStarted = Promise.withResolvers<void>()
        const handleResult = async () => {
            callbackStarted.resolve()
            await callback.promise
        }
        const onSettled = vi.fn()
        const mutation = useApiMutation({
            mutationFn: async () => {
                if (outcome === 'failure') throw new Error('Write failed')
                return 1
            },
            onSuccess: handleResult,
            onError: handleResult,
            onSettled
        })
        const result = mutation.mutate(undefined)
        await callbackStarted.promise
        clearApiQueryCache()
        const assertion = expect(result).rejects.toMatchObject({ code: 'stale_auth_context' })
        callback.resolve()
        await assertion
        expect(onSettled).not.toHaveBeenCalled()
        expect(mutation.data.value).toBeUndefined()
        expect(mutation.error.value).toBeNull()
        expect(mutation.isPending.value).toBe(false)
    })

    it.each(['onSuccess', 'onSettled'] as const)('does not roll back a successful mutation when %s throws', async (callback) => {
        setApiQueryCacheData('callback-users', [1])
        const callbackError = new Error('Callback failed')
        const onError = vi.fn()
        const onSuccess = vi.fn(() => {
            if (callback === 'onSuccess') throw callbackError
        })
        const onSettled = vi.fn(() => {
            if (callback === 'onSettled') throw callbackError
        })
        const mutation = useApiMutation({
            mutationFn: async () => 2,
            invalidateKeys: ['callback-users'],
            onSuccess,
            onError,
            onSettled
        })

        await expect(mutation.mutate(undefined)).rejects.toBe(callbackError)
        expect(mutation.data.value).toBe(2)
        expect(mutation.error.value).toBeNull()
        expect(mutation.isPending.value).toBe(false)
        expect(getApiQueryCacheData('callback-users')).toBeUndefined()
        expect(onError).not.toHaveBeenCalled()
        expect(onSettled).toHaveBeenCalledExactlyOnceWith(2, null, undefined, undefined)
    })

    it('settles a failed mutation even when its error callback throws', async () => {
        const callbackError = new Error('Rollback failed')
        const onSettled = vi.fn()
        const mutation = useApiMutation({
            mutationFn: async () => {
                throw new Error('Write failed')
            },
            onError: () => {
                throw callbackError
            },
            onSettled
        })

        await expect(mutation.mutate(undefined)).rejects.toBe(callbackError)
        expect(mutation.error.value?.message).toBe('Write failed')
        expect(mutation.isPending.value).toBe(false)
        expect(onSettled).toHaveBeenCalledTimes(1)
    })

    it('requires a mapper for a custom error shape', () => {
        // @ts-expect-error Normalized API errors cannot be returned as strings.
        useApiMutation<number, number, string>({ mutationFn: async (value) => value })
        // @ts-expect-error Query errors need the same explicit mapping boundary.
        useApiQuery<number, number, string>({ key: 'custom-error', queryFn: async () => 1, enabled: false })
    })

    it('tracks overlapping requests and prevents older results from replacing the latest result', async () => {
        const first = Promise.withResolvers<number>()
        const second = Promise.withResolvers<number>()
        const mutation = useApiMutation({ mutationFn: (value: number) => (value === 1 ? first.promise : second.promise) })
        const earlier = mutation.mutate(1)
        const later = mutation.mutate(2)
        second.resolve(2)
        await later
        expect(mutation.isPending.value).toBe(true)
        first.resolve(1)
        await earlier
        expect(mutation.isPending.value).toBe(false)
        expect(mutation.data.value).toBe(2)
    })

    it('does not restore reset state when an outstanding mutation completes', async () => {
        const pending = Promise.withResolvers<number>()
        const mutation = useApiMutation({ mutationFn: () => pending.promise })
        const request = mutation.mutate(undefined)
        mutation.reset()
        expect(mutation.isPending.value).toBe(true)
        pending.resolve(1)
        await request
        expect(mutation.data.value).toBeUndefined()
        expect(mutation.isPending.value).toBe(false)
    })

    it('supports optimistic updates with rollback on error', async () => {
        setApiQueryCacheData<number[]>('users:list', [1])

        const mutation = useApiMutation<number, { id: number }, { message: string }, number[]>({
            mutationFn: vi.fn(async () => {
                throw new Error('Mutation failed')
            }),
            onMutate: async (value) => {
                const previousUsers = getApiQueryCacheData<number[]>('users:list') ?? []
                setApiQueryCacheData<number[]>('users:list', [...previousUsers, value])
                return previousUsers
            },
            onError: async (_error, _variables, context) => {
                setApiQueryCacheData('users:list', context)
            },
            mapError: (error) => ({
                message: error instanceof Error ? error.message : 'Unknown error'
            })
        })

        await expect(mutation.mutate(2)).rejects.toEqual({ message: 'Mutation failed' })

        expect(getApiQueryCacheData<number[]>('users:list')).toEqual([1])
    })
})
