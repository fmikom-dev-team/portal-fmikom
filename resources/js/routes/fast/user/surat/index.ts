import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
 * @route '/fast/user/surat/{id}/cancel'
 */
export const cancel = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/fast/user/surat/{id}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
 * @route '/fast/user/surat/{id}/cancel'
 */
cancel.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return cancel.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
 * @route '/fast/user/surat/{id}/cancel'
 */
cancel.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
 * @route '/fast/user/surat/{id}/cancel'
 */
    const cancelForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
 * @route '/fast/user/surat/{id}/cancel'
 */
        cancelForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, options),
            method: 'post',
        })
    
    cancel.form = cancelForm
const surat = {
    cancel: Object.assign(cancel, cancel),
}

export default surat