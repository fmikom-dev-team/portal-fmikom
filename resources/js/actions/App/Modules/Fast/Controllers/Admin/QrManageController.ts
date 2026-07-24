import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
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