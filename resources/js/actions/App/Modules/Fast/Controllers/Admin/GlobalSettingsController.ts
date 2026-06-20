import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/admin/settings/template',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
const GlobalSettingsController = { save }

export default GlobalSettingsController