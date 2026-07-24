import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/dosen/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::create
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:28
 * @route '/dosen/ajukan'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:121
 * @route '/dosen/submissions'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dosen/submissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:121
 * @route '/dosen/submissions'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:121
 * @route '/dosen/submissions'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:121
 * @route '/dosen/submissions'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:121
 * @route '/dosen/submissions'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const SubmissionController = { create, store }

export default SubmissionController