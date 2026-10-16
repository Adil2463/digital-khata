import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
const HomeController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: HomeController.url(options),
    method: 'get',
})

HomeController.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
HomeController.url = (options?: RouteQueryOptions) => {
    return HomeController.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
HomeController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: HomeController.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
HomeController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: HomeController.url(options),
    method: 'head',
})

export default HomeController