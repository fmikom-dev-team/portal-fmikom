import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:39
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
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:39
* @route '/admin/settings/template'
*/
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:39
* @route '/admin/settings/template'
*/
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
* @route '/admin/settings/template/logo-preview'
*/
export const previewLogo = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo.url(options),
    method: 'get',
})

previewLogo.definition = {
    methods: ["get","head"],
    url: '/admin/settings/template/logo-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
* @route '/admin/settings/template/logo-preview'
*/
previewLogo.url = (options?: RouteQueryOptions) => {
    return previewLogo.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
* @route '/admin/settings/template/logo-preview'
*/
previewLogo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
* @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
* @route '/admin/settings/template/logo-preview'
*/
previewLogo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewLogo.url(options),
    method: 'head',
})

const GlobalSettingsController = { save, previewLogo }

export default GlobalSettingsController