import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
* @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
* @route '/admin/history'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
* @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
* @route '/admin/history'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
* @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
* @route '/admin/history'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::index
* @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
* @route '/admin/history'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const HistoryController = { index }

export default HistoryController