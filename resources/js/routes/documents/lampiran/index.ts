import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
export const preview = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/documents/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
preview.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return preview.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
preview.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
preview.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
    const previewForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: preview.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
        previewForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::preview
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:47
 * @route '/documents/lampiran/{id}/preview'
 */
        previewForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    preview.form = previewForm
const lampiran = {
    preview: Object.assign(preview, preview),
}

export default lampiran