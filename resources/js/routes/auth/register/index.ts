import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::create
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:23
* @route '/auth/register'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/auth/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::create
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:23
* @route '/auth/register'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::create
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:23
* @route '/auth/register'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::create
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:23
* @route '/auth/register'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::store
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:28
* @route '/auth/register'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::store
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:28
* @route '/auth/register'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\RegisteredUserController::store
* @see app/Modules/Auth/Http/Controllers/RegisteredUserController.php:28
* @route '/auth/register'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const register = {
    create: Object.assign(create, create),
    store: Object.assign(store, store),
}

export default register