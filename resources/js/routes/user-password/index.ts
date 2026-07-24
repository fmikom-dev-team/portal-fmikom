import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
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
const userPassword = {
    update: Object.assign(update, update),
}

export default userPassword