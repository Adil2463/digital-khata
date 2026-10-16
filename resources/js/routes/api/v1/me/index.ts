import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

const me = {
    show: Object.assign(show, show),
}

export default me