import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::show
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/auth/confirm-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::show
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::show
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::show
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::store
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:29
* @route '/auth/confirm-password'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/auth/confirm-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::store
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:29
* @route '/auth/confirm-password'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::store
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:29
* @route '/auth/confirm-password'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const ConfirmablePasswordController = { show, store }

export default ConfirmablePasswordController