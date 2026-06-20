import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/surat',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:15
 * @route '/admin/surat'
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
const LetterIndexController = { index }

export default LetterIndexController