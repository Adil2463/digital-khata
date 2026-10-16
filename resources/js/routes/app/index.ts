import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import settings from './settings'
import admin from './admin'
/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/app/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Dashboard\Http\Controllers\DashboardController::__invoke
* @see app/Modules/Dashboard/Http/Controllers/DashboardController.php:13
* @route '/app/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

const app = {
    dashboard: Object.assign(dashboard, dashboard),
    settings: Object.assign(settings, settings),
    admin: Object.assign(admin, admin),
}

export default app