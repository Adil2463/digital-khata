import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::index
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:28
* @route '/api/v1/admin/users'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::index
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:28
* @route '/api/v1/admin/users'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::index
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:28
* @route '/api/v1/admin/users'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::index
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:28
* @route '/api/v1/admin/users'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::store
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:35
* @route '/api/v1/admin/users'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/admin/users',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::store
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:35
* @route '/api/v1/admin/users'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::store
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:35
* @route '/api/v1/admin/users'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::update
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:45
* @route '/api/v1/admin/users/{user}'
*/
export const update = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/v1/admin/users/{user}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::update
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:45
* @route '/api/v1/admin/users/{user}'
*/
update.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return update.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::update
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:45
* @route '/api/v1/admin/users/{user}'
*/
update.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::destroy
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:56
* @route '/api/v1/admin/users/{user}'
*/
export const destroy = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/admin/users/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::destroy
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:56
* @route '/api/v1/admin/users/{user}'
*/
destroy.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return destroy.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\AdminUserController::destroy
* @see app/Modules/Api/V1/Http/Controllers/AdminUserController.php:56
* @route '/api/v1/admin/users/{user}'
*/
destroy.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const AdminUserController = { index, store, update, destroy }

export default AdminUserController