import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import confirmD7e05f from './confirm'
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
    confirm: Object.assign(confirm, confirmD7e05f),
}

export default password