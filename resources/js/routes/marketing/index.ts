import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Marketing\Http\Controllers\HomeController::__invoke
* @see app/Modules/Marketing/Http/Controllers/HomeController.php:13
* @route '/'
*/
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

const marketing = {
    home: Object.assign(home, home),
}

export default marketing