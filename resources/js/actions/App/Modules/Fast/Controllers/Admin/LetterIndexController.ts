import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:16
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
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:16
 * @route '/admin/surat'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:16
 * @route '/admin/surat'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
 * @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:16
 * @route '/admin/surat'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const LetterIndexController = { index }

export default LetterIndexController