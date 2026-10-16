import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::edit
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:19
* @route '/app/settings/password'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/app/settings/password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::edit
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:19
* @route '/app/settings/password'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::edit
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:19
* @route '/app/settings/password'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::edit
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:19
* @route '/app/settings/password'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::update
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:27
* @route '/app/settings/password'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/app/settings/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::update
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:27
* @route '/app/settings/password'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Http\Controllers\PasswordController::update
* @see app/Modules/Settings/Http/Controllers/PasswordController.php:27
* @route '/app/settings/password'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

const PasswordController = { edit, update }

export default PasswordController