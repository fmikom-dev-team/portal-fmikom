import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
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
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Controllers\SecurityController::edit
* @see app/Modules/Settings/Controllers/SecurityController.php:38
* @route '/settings/security'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

const security = {
    edit: Object.assign(edit, edit),
}

export default security