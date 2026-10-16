import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::create
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/auth/forgot-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::create
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::create
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::create
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::store
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/auth/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::store
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::store
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const PasswordResetLinkController = { create, store }

export default PasswordResetLinkController