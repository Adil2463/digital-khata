import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import register from './register'
import login from './login'
import password from './password'
import verification from './verification'
/**
* @see \App\Modules\Auth\Http\Controllers\AuthenticatedSessionController::logout
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:51
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
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:51
* @route '/auth/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\AuthenticatedSessionController::logout
* @see app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php:51
* @route '/auth/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

const auth = {
    register: Object.assign(register, register),
    login: Object.assign(login, login),
    password: Object.assign(password, password),
    verification: Object.assign(verification, verification),
    logout: Object.assign(logout, logout),
}

export default auth