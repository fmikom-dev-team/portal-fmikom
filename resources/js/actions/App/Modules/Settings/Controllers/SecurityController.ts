import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/security',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Settings\Controllers\SecurityController::update
* @see app/Modules/Settings/Controllers/SecurityController.php:72
* @route '/settings/password'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/settings/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Settings\Controllers\SecurityController::update
* @see app/Modules/Settings/Controllers/SecurityController.php:72
* @route '/settings/password'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\SecurityController::update
* @see app/Modules/Settings/Controllers/SecurityController.php:72
* @route '/settings/password'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Modules\Settings\Controllers\SecurityController::updateEmail
* @see app/Modules/Settings/Controllers/SecurityController.php:94
* @route '/settings/email'
*/
export const updateEmail = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateEmail.url(options),
    method: 'put',
})

updateEmail.definition = {
    methods: ["put"],
    url: '/settings/email',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Settings\Controllers\SecurityController::updateEmail
* @see app/Modules/Settings/Controllers/SecurityController.php:94
* @route '/settings/email'
*/
updateEmail.url = (options?: RouteQueryOptions) => {
    return updateEmail.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\SecurityController::updateEmail
* @see app/Modules/Settings/Controllers/SecurityController.php:94
* @route '/settings/email'
*/
updateEmail.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateEmail.url(options),
    method: 'put',
})

const SecurityController = { edit, update, updateEmail }

export default SecurityController