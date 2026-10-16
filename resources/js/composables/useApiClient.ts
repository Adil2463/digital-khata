import { getRealtimeSocketId } from '@/lib/realtime/config'
import { isObjectRecord } from '@/lib/utils'

export interface ApiError {
    message: string
    status?: number
    code?: string
    fieldErrors?: Record<string, string[]>
}

export type ApiRequestMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export interface ApiRequestOptions {
    url: string
    method?: ApiRequestMethod
    query?: Record<string, string | number | boolean | null | undefined>
    body?: unknown
    headers?: Record<string, string>
    signal?: AbortSignal
}

export type ApiResponseParser<TResponse> = (payload: unknown) => TResponse
type ApiRequestResult<TParser extends ApiResponseParser<unknown> | undefined> = [TParser] extends [ApiResponseParser<infer TResponse>]
    ? TResponse
    : unknown

const isMutatingMethod = (method: ApiRequestMethod): boolean => method !== 'GET'

const resolveCsrfToken = (): string | undefined => {
    if (typeof document === 'undefined') {
        return undefined
    }

    const cookie = document.cookie.split('; ').find((value) => value.startsWith('XSRF-TOKEN='))
    if (cookie === undefined) return undefined

    try {
        return decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) || undefined
    } catch {
        return undefined
    }
}

const resolveSocketId = (): string | undefined => {
    return getRealtimeSocketId()
}

const withQuery = (url: string, query: ApiRequestOptions['query']): string => {
    if (query === undefined) return url

    const hashIndex = url.indexOf('#')
    const fragment = hashIndex === -1 ? '' : url.slice(hashIndex)
    const address = hashIndex === -1 ? url : url.slice(0, hashIndex)
    const queryIndex = address.indexOf('?')
    const pathname = queryIndex === -1 ? address : address.slice(0, queryIndex)
    const searchParams = new URLSearchParams(queryIndex === -1 ? '' : address.slice(queryIndex + 1))

    Object.entries(query).forEach(([key, value]) => {
        if (value === undefined || value === null) {
            return
        }

        searchParams.set(key, String(value))
    })

    const serialized = searchParams.toString()
    return `${pathname}${serialized === '' ? '' : `?${serialized}`}${fragment}`
}

const resolveFieldErrors = (value: unknown): Record<string, string[]> | undefined => {
    if (!isObjectRecord(value)) {
        return undefined
    }

    const fieldErrors: Record<string, string[]> = {}

    Object.entries(value).forEach(([field, fieldValue]) => {
        if (Array.isArray(fieldValue)) {
            const messages = fieldValue.filter((message): message is string => typeof message === 'string' && message.trim() !== '')

            if (messages.length > 0) {
                fieldErrors[field] = messages
            }
        }
    })

    return Object.keys(fieldErrors).length > 0 ? fieldErrors : undefined
}

const buildApiError = (payload: unknown, status?: number): ApiError => {
    if (isObjectRecord(payload)) {
        const message = typeof payload.message === 'string' && payload.message.trim() !== '' ? payload.message : 'Request failed.'
        const error: ApiError = { message }

        if (status !== undefined) {
            error.status = status
        }

        if (typeof payload.code === 'string') {
            error.code = payload.code
        }

        const fieldErrors = resolveFieldErrors(payload.errors)

        if (fieldErrors !== undefined) {
            error.fieldErrors = fieldErrors
        }

        return error
    }

    if (typeof payload === 'string' && payload.trim() !== '') {
        const error: ApiError = {
            message: payload
        }

        if (status !== undefined) {
            error.status = status
        }

        return error
    }

    const fallbackError: ApiError = {
        message: 'Request failed.'
    }

    if (status !== undefined) {
        fallbackError.status = status
    }

    return fallbackError
}

export const normalizeApiError = (error: unknown): ApiError => {
    if (isObjectRecord(error) && typeof error.message === 'string') {
        const normalizedError: ApiError = {
            message: error.message
        }

        if (typeof error.status === 'number') {
            normalizedError.status = error.status
        }

        if (typeof error.code === 'string') {
            normalizedError.code = error.code
        }

        const fieldErrors = resolveFieldErrors(error.fieldErrors)

        if (fieldErrors !== undefined) {
            normalizedError.fieldErrors = fieldErrors
        }

        return normalizedError
    }

    if (error instanceof Error) {
        return {
            message: error.message
        }
    }

    return {
        message: 'An unexpected error occurred.'
    }
}

const buildResponseValidationError = (error: unknown, status: number): ApiError => {
    const parsedMessage =
        error instanceof Error ? error.message : typeof error === 'string' && error.trim() !== '' ? error : 'Response payload validation failed.'

    return {
        message: parsedMessage,
        status,
        code: 'invalid_response_payload'
    }
}

export async function apiRequest<TParser extends ApiResponseParser<unknown> | undefined>(
    options: ApiRequestOptions & { parseResponse?: TParser }
): Promise<ApiRequestResult<TParser>> {
    const requestUrl = withQuery(options.url, options.query)
    const hasBody = options.body !== undefined
    const method = options.method ?? 'GET'
    const isSameOrigin = typeof window !== 'undefined' && new URL(requestUrl, window.location.href).origin === window.location.origin
    const csrfToken = isSameOrigin ? resolveCsrfToken() : undefined
    const socketId = isSameOrigin ? resolveSocketId() : undefined
    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
        ...(isMutatingMethod(method) && csrfToken !== undefined ? { 'X-XSRF-TOKEN': csrfToken } : {}),
        ...options.headers
    }

    if (socketId !== undefined) {
        headers['X-Socket-ID'] = socketId
    }

    const requestInit: RequestInit = {
        method,
        credentials: 'same-origin',
        headers
    }

    if (hasBody) {
        requestInit.body = JSON.stringify(options.body)
    }

    if (options.signal !== undefined) {
        requestInit.signal = options.signal
    }

    const response = await fetch(requestUrl, requestInit)

    const payload =
        response.status === 204
            ? undefined
            : (() => {
                  const contentType = response.headers.get('content-type')
                  const isJson = contentType !== null && contentType.includes('application/json')

                  if (isJson) {
                      return response.json()
                  }

                  return response.text()
              })()

    if (!response.ok) {
        throw buildApiError(await payload, response.status)
    }

    const resolvedPayload = await payload

    if (options.parseResponse === undefined) {
        return resolvedPayload as ApiRequestResult<TParser>
    }

    try {
        return options.parseResponse(resolvedPayload) as ApiRequestResult<TParser>
    } catch (error) {
        throw buildResponseValidationError(error, response.status)
    }
}
