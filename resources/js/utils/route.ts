import type { RouteDefinition } from '@/wayfinder'
import type { Method } from '@inertiajs/core'

export type InertiaMethod = Extract<Method, 'get' | 'post' | 'put' | 'patch' | 'delete'>

export type InertiaRouteDefinition = {
    url: string
    method: InertiaMethod
}

export function toInertiaRouteDefinition(definition: RouteDefinition<InertiaMethod> | InertiaRouteDefinition): InertiaRouteDefinition {
    return {
        url: definition.url,
        method: definition.method
    }
}
