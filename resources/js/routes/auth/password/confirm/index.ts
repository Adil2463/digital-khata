import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
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

const confirm = {
    store: Object.assign(store, store),
}

export default confirm