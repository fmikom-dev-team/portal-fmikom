import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:18
* @route '/admin/qr'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:18
* @route '/admin/qr'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:18
* @route '/admin/qr'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:18
* @route '/admin/qr'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const QrManageController = { index }

export default QrManageController
