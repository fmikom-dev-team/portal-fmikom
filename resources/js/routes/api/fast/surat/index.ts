import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/fast/surat',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\SuratController::index
 * @see app/Http/Controllers/Api/SuratController.php:22
 * @route '/api/fast/surat'
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
* @see \App\Http\Controllers\Api\SuratController::store
 * @see app/Http/Controllers/Api/SuratController.php:61
 * @route '/api/fast/surat'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/fast/surat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::store
 * @see app/Http/Controllers/Api/SuratController.php:61
 * @route '/api/fast/surat'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::store
 * @see app/Http/Controllers/Api/SuratController.php:61
 * @route '/api/fast/surat'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::store
 * @see app/Http/Controllers/Api/SuratController.php:61
 * @route '/api/fast/surat'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::store
 * @see app/Http/Controllers/Api/SuratController.php:61
 * @route '/api/fast/surat'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
export const show = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/fast/surat/{surat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
show.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { surat: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { surat: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    surat: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        surat: typeof args.surat === 'object'
                ? args.surat.id
                : args.surat,
                }

    return show.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
show.get = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
show.head = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
    const showForm = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
        showForm.get = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\SuratController::show
 * @see app/Http/Controllers/Api/SuratController.php:75
 * @route '/api/fast/surat/{surat}'
 */
        showForm.head = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Api\SuratController::adminValidation
 * @see app/Http/Controllers/Api/SuratController.php:111
 * @route '/api/fast/surat/{surat}/admin-validation'
 */
export const adminValidation = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adminValidation.url(args, options),
    method: 'post',
})

adminValidation.definition = {
    methods: ["post"],
    url: '/api/fast/surat/{surat}/admin-validation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::adminValidation
 * @see app/Http/Controllers/Api/SuratController.php:111
 * @route '/api/fast/surat/{surat}/admin-validation'
 */
adminValidation.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { surat: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { surat: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    surat: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        surat: typeof args.surat === 'object'
                ? args.surat.id
                : args.surat,
                }

    return adminValidation.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::adminValidation
 * @see app/Http/Controllers/Api/SuratController.php:111
 * @route '/api/fast/surat/{surat}/admin-validation'
 */
adminValidation.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adminValidation.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::adminValidation
 * @see app/Http/Controllers/Api/SuratController.php:111
 * @route '/api/fast/surat/{surat}/admin-validation'
 */
    const adminValidationForm = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adminValidation.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::adminValidation
 * @see app/Http/Controllers/Api/SuratController.php:111
 * @route '/api/fast/surat/{surat}/admin-validation'
 */
        adminValidationForm.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adminValidation.url(args, options),
            method: 'post',
        })
    
    adminValidation.form = adminValidationForm
/**
* @see \App\Http\Controllers\Api\SuratController::approval
 * @see app/Http/Controllers/Api/SuratController.php:123
 * @route '/api/fast/surat/{surat}/approval'
 */
export const approval = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approval.url(args, options),
    method: 'post',
})

approval.definition = {
    methods: ["post"],
    url: '/api/fast/surat/{surat}/approval',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::approval
 * @see app/Http/Controllers/Api/SuratController.php:123
 * @route '/api/fast/surat/{surat}/approval'
 */
approval.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { surat: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { surat: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    surat: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        surat: typeof args.surat === 'object'
                ? args.surat.id
                : args.surat,
                }

    return approval.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::approval
 * @see app/Http/Controllers/Api/SuratController.php:123
 * @route '/api/fast/surat/{surat}/approval'
 */
approval.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approval.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::approval
 * @see app/Http/Controllers/Api/SuratController.php:123
 * @route '/api/fast/surat/{surat}/approval'
 */
    const approvalForm = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approval.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::approval
 * @see app/Http/Controllers/Api/SuratController.php:123
 * @route '/api/fast/surat/{surat}/approval'
 */
        approvalForm.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approval.url(args, options),
            method: 'post',
        })
    
    approval.form = approvalForm
/**
* @see \App\Http\Controllers\Api\SuratController::generateDocument
 * @see app/Http/Controllers/Api/SuratController.php:137
 * @route '/api/fast/surat/{surat}/generate-document'
 */
export const generateDocument = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDocument.url(args, options),
    method: 'post',
})

generateDocument.definition = {
    methods: ["post"],
    url: '/api/fast/surat/{surat}/generate-document',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::generateDocument
 * @see app/Http/Controllers/Api/SuratController.php:137
 * @route '/api/fast/surat/{surat}/generate-document'
 */
generateDocument.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { surat: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { surat: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    surat: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        surat: typeof args.surat === 'object'
                ? args.surat.id
                : args.surat,
                }

    return generateDocument.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::generateDocument
 * @see app/Http/Controllers/Api/SuratController.php:137
 * @route '/api/fast/surat/{surat}/generate-document'
 */
generateDocument.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDocument.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SuratController::generateDocument
 * @see app/Http/Controllers/Api/SuratController.php:137
 * @route '/api/fast/surat/{surat}/generate-document'
 */
    const generateDocumentForm = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generateDocument.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SuratController::generateDocument
 * @see app/Http/Controllers/Api/SuratController.php:137
 * @route '/api/fast/surat/{surat}/generate-document'
 */
        generateDocumentForm.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generateDocument.url(args, options),
            method: 'post',
        })
    
    generateDocument.form = generateDocumentForm
const surat = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
adminValidation: Object.assign(adminValidation, adminValidation),
approval: Object.assign(approval, approval),
generateDocument: Object.assign(generateDocument, generateDocument),
}

export default surat