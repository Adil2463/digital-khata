import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationNotificationController::store
* @see app/Modules/Auth/Http/Controllers/EmailVerificationNotificationController.php:17
* @route '/auth/email/verification-notification'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/auth/email/verification-notification',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationNotificationController::store
* @see app/Modules/Auth/Http/Controllers/EmailVerificationNotificationController.php:17
* @route '/auth/email/verification-notification'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationNotificationController::store
* @see app/Modules/Auth/Http/Controllers/EmailVerificationNotificationController.php:17
* @route '/auth/email/verification-notification'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const EmailVerificationNotificationController = { store }

export default EmailVerificationNotificationController