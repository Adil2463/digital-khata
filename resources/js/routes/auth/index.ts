import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import login from './login'
import verification from './verification'
import password from './password'
/**
* @see \App\Modules\Auth\Http\Controllers\AuthenticatedSessionController::logout
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:49
* @route '/auth/logout'
*/
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\AuthenticatedSessionController::logout
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:49
* @route '/auth/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\AuthenticatedSessionController::logout
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:49
* @route '/auth/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

const auth = {
    login: Object.assign(login, login),
    verification: Object.assign(verification, verification),
    password: Object.assign(password, password),
    logout: Object.assign(logout, logout),
}

export default auth