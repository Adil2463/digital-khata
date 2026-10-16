import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Users\Http\Controllers\UserController::index
* @see app/Modules/Users/Http/Controllers/UserController.php:28
* @route '/app/admin/users'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/app/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Users\Http\Controllers\UserController::index
* @see app/Modules/Users/Http/Controllers/UserController.php:28
* @route '/app/admin/users'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Users\Http\Controllers\UserController::index
* @see app/Modules/Users/Http/Controllers/UserController.php:28
* @route '/app/admin/users'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Users\Http\Controllers\UserController::index
* @see app/Modules/Users/Http/Controllers/UserController.php:28
* @route '/app/admin/users'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Users\Http\Controllers\UserController::store
* @see app/Modules/Users/Http/Controllers/UserController.php:38
* @route '/app/admin/users'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/app/admin/users',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Users\Http\Controllers\UserController::store
* @see app/Modules/Users/Http/Controllers/UserController.php:38
* @route '/app/admin/users'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Users\Http\Controllers\UserController::store
* @see app/Modules/Users/Http/Controllers/UserController.php:38
* @route '/app/admin/users'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Users\Http\Controllers\UserController::update
* @see app/Modules/Users/Http/Controllers/UserController.php:49
* @route '/app/admin/users/{user}'
*/
export const update = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/app/admin/users/{user}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Modules\Users\Http\Controllers\UserController::update
* @see app/Modules/Users/Http/Controllers/UserController.php:49
* @route '/app/admin/users/{user}'
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
* @see \App\Modules\Users\Http\Controllers\UserController::update
* @see app/Modules/Users/Http/Controllers/UserController.php:49
* @route '/app/admin/users/{user}'
*/
update.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Modules\Users\Http\Controllers\UserController::update
* @see app/Modules/Users/Http/Controllers/UserController.php:49
* @route '/app/admin/users/{user}'
*/
update.patch = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Modules\Users\Http\Controllers\UserController::destroy
* @see app/Modules/Users/Http/Controllers/UserController.php:61
* @route '/app/admin/users/{user}'
*/
export const destroy = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/app/admin/users/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Users\Http\Controllers\UserController::destroy
* @see app/Modules/Users/Http/Controllers/UserController.php:61
* @route '/app/admin/users/{user}'
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
* @see \App\Modules\Users\Http\Controllers\UserController::destroy
* @see app/Modules/Users/Http/Controllers/UserController.php:61
* @route '/app/admin/users/{user}'
*/
destroy.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const UserController = { index, store, update, destroy }

export default UserController