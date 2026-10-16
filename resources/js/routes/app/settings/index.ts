import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import profile from './profile'
import password from './password'
/**
* @see app/Modules/Settings/Routes/web.php:24
* @route '/app/settings/appearance'
*/
export const appearance = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
})

appearance.definition = {
    methods: ["get","head"],
    url: '/app/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see app/Modules/Settings/Routes/web.php:24
* @route '/app/settings/appearance'
*/
appearance.url = (options?: RouteQueryOptions) => {
    return appearance.definition.url + queryParams(options)
}

/**
* @see app/Modules/Settings/Routes/web.php:24
* @route '/app/settings/appearance'
*/
appearance.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
})

/**
* @see app/Modules/Settings/Routes/web.php:24
* @route '/app/settings/appearance'
*/
appearance.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: appearance.url(options),
    method: 'head',
})

const settings = {
    profile: Object.assign(profile, profile),
    password: Object.assign(password, password),
    appearance: Object.assign(appearance, appearance),
}

export default settings