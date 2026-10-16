import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import confirmD7e05f from './confirm'
/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::request
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
export const request = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: request.url(options),
    method: 'get',
})

request.definition = {
    methods: ["get","head"],
    url: '/auth/forgot-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::request
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
request.url = (options?: RouteQueryOptions) => {
    return request.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::request
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
request.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: request.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::request
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:22
* @route '/auth/forgot-password'
*/
request.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: request.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::email
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
export const email = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: email.url(options),
    method: 'post',
})

email.definition = {
    methods: ["post"],
    url: '/auth/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::email
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
email.url = (options?: RouteQueryOptions) => {
    return email.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\PasswordResetLinkController::email
* @see app/Modules/Auth/Http/Controllers/PasswordResetLinkController.php:32
* @route '/auth/forgot-password'
*/
email.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: email.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::reset
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:27
* @route '/auth/reset-password/{token}'
*/
export const reset = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(args, options),
    method: 'get',
})

reset.definition = {
    methods: ["get","head"],
    url: '/auth/reset-password/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::reset
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:27
* @route '/auth/reset-password/{token}'
*/
reset.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return reset.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::reset
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:27
* @route '/auth/reset-password/{token}'
*/
reset.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::reset
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:27
* @route '/auth/reset-password/{token}'
*/
reset.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reset.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::store
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:49
* @route '/auth/reset-password'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/auth/reset-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::store
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:49
* @route '/auth/reset-password'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\NewPasswordController::store
* @see app/Modules/Auth/Http/Controllers/NewPasswordController.php:49
* @route '/auth/reset-password'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::confirm
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})

confirm.definition = {
    methods: ["get","head"],
    url: '/auth/confirm-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::confirm
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::confirm
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
confirm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\ConfirmablePasswordController::confirm
* @see app/Modules/Auth/Http/Controllers/ConfirmablePasswordController.php:21
* @route '/auth/confirm-password'
*/
confirm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirm.url(options),
    method: 'head',
})

const password = {
    request: Object.assign(request, request),
    email: Object.assign(email, email),
    reset: Object.assign(reset, reset),
    store: Object.assign(store, store),
    confirm: Object.assign(confirm, confirmD7e05f),
}

export default password