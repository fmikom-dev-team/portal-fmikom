import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/admin/history'
 */
const index712511a254a7dfe7f3332db4142a47d9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index712511a254a7dfe7f3332db4142a47d9.url(options),
    method: 'get',
})

index712511a254a7dfe7f3332db4142a47d9.definition = {
    methods: ["get","head"],
    url: '/admin/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/admin/history'
 */
index712511a254a7dfe7f3332db4142a47d9.url = (options?: RouteQueryOptions) => {
    return index712511a254a7dfe7f3332db4142a47d9.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/admin/history'
 */
index712511a254a7dfe7f3332db4142a47d9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index712511a254a7dfe7f3332db4142a47d9.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/admin/history'
 */
index712511a254a7dfe7f3332db4142a47d9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index712511a254a7dfe7f3332db4142a47d9.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/kaprodi/admin/history'
 */
const index53f298859ccc2f4dcada3a2e3ebc0d43 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index53f298859ccc2f4dcada3a2e3ebc0d43.url(options),
    method: 'get',
})

index53f298859ccc2f4dcada3a2e3ebc0d43.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/kaprodi/admin/history'
 */
index53f298859ccc2f4dcada3a2e3ebc0d43.url = (options?: RouteQueryOptions) => {
    return index53f298859ccc2f4dcada3a2e3ebc0d43.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/kaprodi/admin/history'
 */
index53f298859ccc2f4dcada3a2e3ebc0d43.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index53f298859ccc2f4dcada3a2e3ebc0d43.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/kaprodi/admin/history'
 */
index53f298859ccc2f4dcada3a2e3ebc0d43.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index53f298859ccc2f4dcada3a2e3ebc0d43.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/dekan/admin/history'
 */
const indexb09fcad8e23f39d34728cc5e24fd4377 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb09fcad8e23f39d34728cc5e24fd4377.url(options),
    method: 'get',
})

indexb09fcad8e23f39d34728cc5e24fd4377.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/dekan/admin/history'
 */
indexb09fcad8e23f39d34728cc5e24fd4377.url = (options?: RouteQueryOptions) => {
    return indexb09fcad8e23f39d34728cc5e24fd4377.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/dekan/admin/history'
 */
indexb09fcad8e23f39d34728cc5e24fd4377.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb09fcad8e23f39d34728cc5e24fd4377.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:15
 * @route '/dekan/admin/history'
 */
indexb09fcad8e23f39d34728cc5e24fd4377.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexb09fcad8e23f39d34728cc5e24fd4377.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\HistoryController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/history': index712511a254a7dfe7f3332db4142a47d9,
    '/kaprodi/admin/history': index53f298859ccc2f4dcada3a2e3ebc0d43,
    '/dekan/admin/history': indexb09fcad8e23f39d34728cc5e24fd4377,
}

const HistoryController = { index }

export default HistoryController