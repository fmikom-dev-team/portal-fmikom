import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
const index4c30f5087ac3163e9a371a2d63a361c7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index4c30f5087ac3163e9a371a2d63a361c7.url(options),
    method: 'get',
})

index4c30f5087ac3163e9a371a2d63a361c7.definition = {
    methods: ["get","head"],
    url: '/admin/qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
index4c30f5087ac3163e9a371a2d63a361c7.url = (options?: RouteQueryOptions) => {
    return index4c30f5087ac3163e9a371a2d63a361c7.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
index4c30f5087ac3163e9a371a2d63a361c7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index4c30f5087ac3163e9a371a2d63a361c7.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
index4c30f5087ac3163e9a371a2d63a361c7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index4c30f5087ac3163e9a371a2d63a361c7.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
    const index4c30f5087ac3163e9a371a2d63a361c7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index4c30f5087ac3163e9a371a2d63a361c7.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
        index4c30f5087ac3163e9a371a2d63a361c7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index4c30f5087ac3163e9a371a2d63a361c7.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/admin/qr'
 */
        index4c30f5087ac3163e9a371a2d63a361c7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index4c30f5087ac3163e9a371a2d63a361c7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index4c30f5087ac3163e9a371a2d63a361c7.form = index4c30f5087ac3163e9a371a2d63a361c7Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
const indexf01271985537308e0e3d76108f698bb1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf01271985537308e0e3d76108f698bb1.url(options),
    method: 'get',
})

indexf01271985537308e0e3d76108f698bb1.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
indexf01271985537308e0e3d76108f698bb1.url = (options?: RouteQueryOptions) => {
    return indexf01271985537308e0e3d76108f698bb1.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
indexf01271985537308e0e3d76108f698bb1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf01271985537308e0e3d76108f698bb1.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
indexf01271985537308e0e3d76108f698bb1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexf01271985537308e0e3d76108f698bb1.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
    const indexf01271985537308e0e3d76108f698bb1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexf01271985537308e0e3d76108f698bb1.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
        indexf01271985537308e0e3d76108f698bb1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf01271985537308e0e3d76108f698bb1.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/kaprodi/admin/qr'
 */
        indexf01271985537308e0e3d76108f698bb1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf01271985537308e0e3d76108f698bb1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexf01271985537308e0e3d76108f698bb1.form = indexf01271985537308e0e3d76108f698bb1Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
const indexd9576a753905748ddd0358631b11248a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd9576a753905748ddd0358631b11248a.url(options),
    method: 'get',
})

indexd9576a753905748ddd0358631b11248a.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
indexd9576a753905748ddd0358631b11248a.url = (options?: RouteQueryOptions) => {
    return indexd9576a753905748ddd0358631b11248a.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
indexd9576a753905748ddd0358631b11248a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd9576a753905748ddd0358631b11248a.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
indexd9576a753905748ddd0358631b11248a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexd9576a753905748ddd0358631b11248a.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
    const indexd9576a753905748ddd0358631b11248aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexd9576a753905748ddd0358631b11248a.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
        indexd9576a753905748ddd0358631b11248aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexd9576a753905748ddd0358631b11248a.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
 * @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
 * @route '/dekan/admin/qr'
 */
        indexd9576a753905748ddd0358631b11248aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexd9576a753905748ddd0358631b11248a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexd9576a753905748ddd0358631b11248a.form = indexd9576a753905748ddd0358631b11248aForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\QrManageController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/qr': index4c30f5087ac3163e9a371a2d63a361c7,
    '/kaprodi/admin/qr': indexf01271985537308e0e3d76108f698bb1,
    '/dekan/admin/qr': indexd9576a753905748ddd0358631b11248a,
}

const QrManageController = { index }

export default QrManageController