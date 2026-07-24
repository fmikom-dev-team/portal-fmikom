import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
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
* @see \App\Modules\Settings\Controllers\SecurityController::update
 * @see app/Modules/Settings/Controllers/SecurityController.php:72
 * @route '/settings/password'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Modules\Settings\Controllers\SecurityController::update
 * @see app/Modules/Settings/Controllers/SecurityController.php:72
 * @route '/settings/password'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const userPassword = {
    update: Object.assign(update, update),
}

export default userPassword