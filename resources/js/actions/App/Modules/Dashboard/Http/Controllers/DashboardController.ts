import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
const DashboardController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardController.url(options),
    method: 'get',
})

DashboardController.definition = {
    methods: ["get","head"],
    url: '/app/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
DashboardController.url = (options?: RouteQueryOptions) => {
    return DashboardController.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
DashboardController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardController.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
DashboardController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: DashboardController.url(options),
    method: 'head',
})

export default DashboardController