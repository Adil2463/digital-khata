import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::edit
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:28
* @route '/app/settings/profile'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/app/settings/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::edit
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:28
* @route '/app/settings/profile'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::edit
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:28
* @route '/app/settings/profile'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::edit
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:28
* @route '/app/settings/profile'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::update
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:44
* @route '/app/settings/profile'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/app/settings/profile',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::update
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:44
* @route '/app/settings/profile'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Http\Controllers\ProfileController::update
* @see app/Modules/Settings/Http/Controllers/ProfileController.php:44
* @route '/app/settings/profile'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

const profile = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default profile