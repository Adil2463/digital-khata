import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationPromptController::__invoke
* @see app/Modules/Auth/Http/Controllers/EmailVerificationPromptController.php:21
* @route '/auth/verify-email'
*/
const EmailVerificationPromptController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EmailVerificationPromptController.url(options),
    method: 'get',
})

EmailVerificationPromptController.definition = {
    methods: ["get","head"],
    url: '/auth/verify-email',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationPromptController::__invoke
* @see app/Modules/Auth/Http/Controllers/EmailVerificationPromptController.php:21
* @route '/auth/verify-email'
*/
EmailVerificationPromptController.url = (options?: RouteQueryOptions) => {
    return EmailVerificationPromptController.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationPromptController::__invoke
* @see app/Modules/Auth/Http/Controllers/EmailVerificationPromptController.php:21
* @route '/auth/verify-email'
*/
EmailVerificationPromptController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EmailVerificationPromptController.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Auth\Http\Controllers\EmailVerificationPromptController::__invoke
* @see app/Modules/Auth/Http/Controllers/EmailVerificationPromptController.php:21
* @route '/auth/verify-email'
*/
EmailVerificationPromptController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EmailVerificationPromptController.url(options),
    method: 'head',
})

export default EmailVerificationPromptController