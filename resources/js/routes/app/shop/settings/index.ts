import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::edit
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:24
* @route '/app/shop/settings'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/app/shop/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::edit
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:24
* @route '/app/shop/settings'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::edit
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:24
* @route '/app/shop/settings'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::edit
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:24
* @route '/app/shop/settings'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::update
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:32
* @route '/app/shop/settings'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/app/shop/settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::update
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:32
* @route '/app/shop/settings'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Shop\Http\Controllers\ShopSettingsController::update
* @see app/Modules/Shop/Http/Controllers/ShopSettingsController.php:32
* @route '/app/shop/settings'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

const settings = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default settings