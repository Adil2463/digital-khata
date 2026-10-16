import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../../wayfinder'
/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
const MeController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: MeController.url(options),
    method: 'get',
})

MeController.definition = {
    methods: ["get","head"],
    url: '/api/v1/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
MeController.url = (options?: RouteQueryOptions) => {
    return MeController.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
MeController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: MeController.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Api\V1\Http\Controllers\MeController::__invoke
* @see app/Modules/Api/V1/Http/Controllers/MeController.php:16
* @route '/api/v1/me'
*/
MeController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: MeController.url(options),
    method: 'head',
})

export default MeController