import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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

    /**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
 * @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
 * @route '/admin/archive'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
 * @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
 * @route '/admin/archive'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\ArchiveController::index
 * @see app/Modules/Fast/Controllers/Admin/ArchiveController.php:15
 * @route '/admin/archive'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
const archive = {
    index: Object.assign(index, index),
}

export default archive