import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::template
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
export const template = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: template.url(options),
    method: 'post',
})

template.definition = {
    methods: ["post"],
    url: '/admin/settings/template',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::template
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
template.url = (options?: RouteQueryOptions) => {
    return template.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::template
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
template.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: template.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::template
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
    const templateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: template.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::template
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:12
 * @route '/admin/settings/template'
 */
        templateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: template.url(options),
            method: 'post',
        })
    
    template.form = templateForm
const settings = {
    template: Object.assign(template, template),
}

export default settings