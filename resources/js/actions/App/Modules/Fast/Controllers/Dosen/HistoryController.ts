import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dosen/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::index
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
 * @route '/dosen/history'
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
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dosen/history/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
show.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
    const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
        showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
 * @route '/dosen/history/{id}'
 */
        showForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:168
 * @route '/dosen/surat/{id}/cancel'
 */
export const cancel = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/dosen/surat/{id}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:168
 * @route '/dosen/surat/{id}/cancel'
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
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:168
 * @route '/dosen/surat/{id}/cancel'
 */
cancel.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:168
 * @route '/dosen/surat/{id}/cancel'
 */
    const cancelForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::cancel
 * @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:168
 * @route '/dosen/surat/{id}/cancel'
 */
        cancelForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, options),
            method: 'post',
        })
    
    cancel.form = cancelForm
const HistoryController = { index, show, cancel }

export default HistoryController