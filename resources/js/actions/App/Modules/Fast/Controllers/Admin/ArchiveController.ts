import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
* @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
* @route '/admin/archive'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
* @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
* @route '/admin/archive'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
* @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
* @route '/admin/archive'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
* @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
* @route '/admin/archive'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const ArchiveController = { index }

export default ArchiveController