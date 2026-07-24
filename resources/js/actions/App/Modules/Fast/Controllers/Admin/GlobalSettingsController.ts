import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/admin/settings/template'
 */
const save2dfa4b2077acf3792e3877e8343031c0 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save2dfa4b2077acf3792e3877e8343031c0.url(options),
    method: 'post',
})

save2dfa4b2077acf3792e3877e8343031c0.definition = {
    methods: ["post"],
    url: '/admin/settings/template',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/admin/settings/template'
 */
save2dfa4b2077acf3792e3877e8343031c0.url = (options?: RouteQueryOptions) => {
    return save2dfa4b2077acf3792e3877e8343031c0.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/admin/settings/template'
 */
save2dfa4b2077acf3792e3877e8343031c0.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save2dfa4b2077acf3792e3877e8343031c0.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/kaprodi/admin/settings/template'
 */
const save20dfbf2fa591d06e40e119b749465e84 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save20dfbf2fa591d06e40e119b749465e84.url(options),
    method: 'post',
})

save20dfbf2fa591d06e40e119b749465e84.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/settings/template',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/kaprodi/admin/settings/template'
 */
save20dfbf2fa591d06e40e119b749465e84.url = (options?: RouteQueryOptions) => {
    return save20dfbf2fa591d06e40e119b749465e84.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/kaprodi/admin/settings/template'
 */
save20dfbf2fa591d06e40e119b749465e84.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save20dfbf2fa591d06e40e119b749465e84.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/dekan/admin/settings/template'
 */
const savefd82f972326d00b3e471ea88ea953ba5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: savefd82f972326d00b3e471ea88ea953ba5.url(options),
    method: 'post',
})

savefd82f972326d00b3e471ea88ea953ba5.definition = {
    methods: ["post"],
    url: '/dekan/admin/settings/template',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/dekan/admin/settings/template'
 */
savefd82f972326d00b3e471ea88ea953ba5.url = (options?: RouteQueryOptions) => {
    return savefd82f972326d00b3e471ea88ea953ba5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:41
 * @route '/dekan/admin/settings/template'
 */
savefd82f972326d00b3e471ea88ea953ba5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: savefd82f972326d00b3e471ea88ea953ba5.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::save, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `save['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const save = {
    '/admin/settings/template': save2dfa4b2077acf3792e3877e8343031c0,
    '/kaprodi/admin/settings/template': save20dfbf2fa591d06e40e119b749465e84,
    '/dekan/admin/settings/template': savefd82f972326d00b3e471ea88ea953ba5,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/admin/settings/template/logo-preview'
 */
const previewLogoa939e3f916d454fe26eebc11dbbc60f2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogoa939e3f916d454fe26eebc11dbbc60f2.url(options),
    method: 'get',
})

previewLogoa939e3f916d454fe26eebc11dbbc60f2.definition = {
    methods: ["get","head"],
    url: '/admin/settings/template/logo-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/admin/settings/template/logo-preview'
 */
previewLogoa939e3f916d454fe26eebc11dbbc60f2.url = (options?: RouteQueryOptions) => {
    return previewLogoa939e3f916d454fe26eebc11dbbc60f2.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/admin/settings/template/logo-preview'
 */
previewLogoa939e3f916d454fe26eebc11dbbc60f2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogoa939e3f916d454fe26eebc11dbbc60f2.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/admin/settings/template/logo-preview'
 */
previewLogoa939e3f916d454fe26eebc11dbbc60f2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewLogoa939e3f916d454fe26eebc11dbbc60f2.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/kaprodi/admin/settings/template/logo-preview'
 */
const previewLogo7d354da9cfd70d8ec0ac103079938964 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo7d354da9cfd70d8ec0ac103079938964.url(options),
    method: 'get',
})

previewLogo7d354da9cfd70d8ec0ac103079938964.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/settings/template/logo-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/kaprodi/admin/settings/template/logo-preview'
 */
previewLogo7d354da9cfd70d8ec0ac103079938964.url = (options?: RouteQueryOptions) => {
    return previewLogo7d354da9cfd70d8ec0ac103079938964.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/kaprodi/admin/settings/template/logo-preview'
 */
previewLogo7d354da9cfd70d8ec0ac103079938964.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo7d354da9cfd70d8ec0ac103079938964.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/kaprodi/admin/settings/template/logo-preview'
 */
previewLogo7d354da9cfd70d8ec0ac103079938964.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewLogo7d354da9cfd70d8ec0ac103079938964.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/dekan/admin/settings/template/logo-preview'
 */
const previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.url(options),
    method: 'get',
})

previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/settings/template/logo-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/dekan/admin/settings/template/logo-preview'
 */
previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.url = (options?: RouteQueryOptions) => {
    return previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/dekan/admin/settings/template/logo-preview'
 */
previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo
 * @see app/Modules/Fast/Controllers/Admin/GlobalSettingsController.php:15
 * @route '/dekan/admin/settings/template/logo-preview'
 */
previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\GlobalSettingsController::previewLogo, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewLogo['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewLogo = {
    '/admin/settings/template/logo-preview': previewLogoa939e3f916d454fe26eebc11dbbc60f2,
    '/kaprodi/admin/settings/template/logo-preview': previewLogo7d354da9cfd70d8ec0ac103079938964,
    '/dekan/admin/settings/template/logo-preview': previewLogo9eb4b7eb5c948d4a4bb5f17bf3704869,
}

const GlobalSettingsController = { save, previewLogo }

export default GlobalSettingsController