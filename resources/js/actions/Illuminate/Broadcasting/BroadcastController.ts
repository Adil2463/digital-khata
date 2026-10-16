import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/api/broadcasting/auth'
*/
const authenticate7e6d5e884dedc4c100d439e039b017c3 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate7e6d5e884dedc4c100d439e039b017c3.url(options),
    method: 'get',
})

authenticate7e6d5e884dedc4c100d439e039b017c3.definition = {
    methods: ["get","post","head"],
    url: '/api/broadcasting/auth',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/api/broadcasting/auth'
*/
authenticate7e6d5e884dedc4c100d439e039b017c3.url = (options?: RouteQueryOptions) => {
    return authenticate7e6d5e884dedc4c100d439e039b017c3.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/api/broadcasting/auth'
*/
authenticate7e6d5e884dedc4c100d439e039b017c3.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate7e6d5e884dedc4c100d439e039b017c3.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/api/broadcasting/auth'
*/
authenticate7e6d5e884dedc4c100d439e039b017c3.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: authenticate7e6d5e884dedc4c100d439e039b017c3.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/api/broadcasting/auth'
*/
authenticate7e6d5e884dedc4c100d439e039b017c3.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: authenticate7e6d5e884dedc4c100d439e039b017c3.url(options),
    method: 'head',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
const authenticate95142b6115a9d019b8204096de0eb7b5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate95142b6115a9d019b8204096de0eb7b5.url(options),
    method: 'get',
})

authenticate95142b6115a9d019b8204096de0eb7b5.definition = {
    methods: ["get","post","head"],
    url: '/broadcasting/auth',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate95142b6115a9d019b8204096de0eb7b5.url = (options?: RouteQueryOptions) => {
    return authenticate95142b6115a9d019b8204096de0eb7b5.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate95142b6115a9d019b8204096de0eb7b5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate95142b6115a9d019b8204096de0eb7b5.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate95142b6115a9d019b8204096de0eb7b5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: authenticate95142b6115a9d019b8204096de0eb7b5.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate95142b6115a9d019b8204096de0eb7b5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: authenticate95142b6115a9d019b8204096de0eb7b5.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \Illuminate\Broadcasting\BroadcastController::authenticate, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `authenticate['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const authenticate = {
    '/api/broadcasting/auth': authenticate7e6d5e884dedc4c100d439e039b017c3,
    '/broadcasting/auth': authenticate95142b6115a9d019b8204096de0eb7b5,
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticateUser
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:37
* @route '/api/broadcasting/user-auth'
*/
export const authenticateUser = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticateUser.url(options),
    method: 'get',
})

authenticateUser.definition = {
    methods: ["get","post","head"],
    url: '/api/broadcasting/user-auth',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticateUser
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:37
* @route '/api/broadcasting/user-auth'
*/
authenticateUser.url = (options?: RouteQueryOptions) => {
    return authenticateUser.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticateUser
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:37
* @route '/api/broadcasting/user-auth'
*/
authenticateUser.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticateUser.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticateUser
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:37
* @route '/api/broadcasting/user-auth'
*/
authenticateUser.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: authenticateUser.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticateUser
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:37
* @route '/api/broadcasting/user-auth'
*/
authenticateUser.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: authenticateUser.url(options),
    method: 'head',
})

const BroadcastController = { authenticate, authenticateUser }

export default BroadcastController