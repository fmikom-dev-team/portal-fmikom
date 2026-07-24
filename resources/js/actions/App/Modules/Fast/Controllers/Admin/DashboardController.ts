import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
const previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
    method: 'get',
})

previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.definition = {
    methods: ["get","head"],
    url: '/documents/public/surat/{id}/template-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
    const previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
        previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/public/surat/{id}/template-preview'
 */
        previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.form = previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
const previewTemplateee614e014c85387e34677786d92b7354 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
    method: 'get',
})

previewTemplateee614e014c85387e34677786d92b7354.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/template-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
previewTemplateee614e014c85387e34677786d92b7354.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewTemplateee614e014c85387e34677786d92b7354.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
previewTemplateee614e014c85387e34677786d92b7354.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
previewTemplateee614e014c85387e34677786d92b7354.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
    const previewTemplateee614e014c85387e34677786d92b7354Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
        previewTemplateee614e014c85387e34677786d92b7354Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/documents/surat/{id}/template-preview'
 */
        previewTemplateee614e014c85387e34677786d92b7354Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplateee614e014c85387e34677786d92b7354.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewTemplateee614e014c85387e34677786d92b7354.form = previewTemplateee614e014c85387e34677786d92b7354Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
const previewTemplate5ca38ce12df99acea33c48ceb303e37d = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
    method: 'get',
})

previewTemplate5ca38ce12df99acea33c48ceb303e37d.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/template-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
previewTemplate5ca38ce12df99acea33c48ceb303e37d.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewTemplate5ca38ce12df99acea33c48ceb303e37d.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
previewTemplate5ca38ce12df99acea33c48ceb303e37d.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
previewTemplate5ca38ce12df99acea33c48ceb303e37d.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
    const previewTemplate5ca38ce12df99acea33c48ceb303e37dForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
        previewTemplate5ca38ce12df99acea33c48ceb303e37dForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:38
 * @route '/admin/surat/{id}/template-preview'
 */
        previewTemplate5ca38ce12df99acea33c48ceb303e37dForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewTemplate5ca38ce12df99acea33c48ceb303e37d.form = previewTemplate5ca38ce12df99acea33c48ceb303e37dForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewTemplate['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewTemplate = {
    '/documents/public/surat/{id}/template-preview': previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8,
    '/documents/surat/{id}/template-preview': previewTemplateee614e014c85387e34677786d92b7354,
    '/admin/surat/{id}/template-preview': previewTemplate5ca38ce12df99acea33c48ceb303e37d,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
const previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
    method: 'get',
})

previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.definition = {
    methods: ["get","head"],
    url: '/documents/public/surat/{id}/generated-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
    const previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6aForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
        previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6aForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/public/surat/{id}/generated-document'
 */
        previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6aForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.form = previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6aForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
const previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
    method: 'get',
})

previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/generated-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
    const previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
        previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/surat/{id}/generated-document'
 */
        previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.form = previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
const previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
    method: 'get',
})

previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/generated-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
    const previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
        previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/surat/{id}/generated-document'
 */
        previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.form = previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewGeneratedDocument['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewGeneratedDocument = {
    '/documents/public/surat/{id}/generated-document': previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a,
    '/documents/surat/{id}/generated-document': previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067,
    '/admin/surat/{id}/generated-document': previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
const downloadPdf175f3b2747492debcba34c78c9a49c61 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
    method: 'get',
})

downloadPdf175f3b2747492debcba34c78c9a49c61.definition = {
    methods: ["get","head"],
    url: '/documents/public/surat/{id}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
downloadPdf175f3b2747492debcba34c78c9a49c61.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadPdf175f3b2747492debcba34c78c9a49c61.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
downloadPdf175f3b2747492debcba34c78c9a49c61.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
downloadPdf175f3b2747492debcba34c78c9a49c61.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
    const downloadPdf175f3b2747492debcba34c78c9a49c61Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
        downloadPdf175f3b2747492debcba34c78c9a49c61Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/public/surat/{id}/pdf'
 */
        downloadPdf175f3b2747492debcba34c78c9a49c61Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadPdf175f3b2747492debcba34c78c9a49c61.form = downloadPdf175f3b2747492debcba34c78c9a49c61Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
const downloadPdfccfc3e9865ca14301019c9d23437884e = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
    method: 'get',
})

downloadPdfccfc3e9865ca14301019c9d23437884e.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
downloadPdfccfc3e9865ca14301019c9d23437884e.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadPdfccfc3e9865ca14301019c9d23437884e.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
downloadPdfccfc3e9865ca14301019c9d23437884e.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
downloadPdfccfc3e9865ca14301019c9d23437884e.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
    const downloadPdfccfc3e9865ca14301019c9d23437884eForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
        downloadPdfccfc3e9865ca14301019c9d23437884eForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/documents/surat/{id}/pdf'
 */
        downloadPdfccfc3e9865ca14301019c9d23437884eForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadPdfccfc3e9865ca14301019c9d23437884e.form = downloadPdfccfc3e9865ca14301019c9d23437884eForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
const downloadPdf05d105208d0811647b427cb9e4a2a116 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
    method: 'get',
})

downloadPdf05d105208d0811647b427cb9e4a2a116.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
downloadPdf05d105208d0811647b427cb9e4a2a116.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadPdf05d105208d0811647b427cb9e4a2a116.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
downloadPdf05d105208d0811647b427cb9e4a2a116.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
downloadPdf05d105208d0811647b427cb9e4a2a116.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
    const downloadPdf05d105208d0811647b427cb9e4a2a116Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
        downloadPdf05d105208d0811647b427cb9e4a2a116Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:54
 * @route '/admin/surat/{id}/pdf'
 */
        downloadPdf05d105208d0811647b427cb9e4a2a116Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadPdf05d105208d0811647b427cb9e4a2a116.form = downloadPdf05d105208d0811647b427cb9e4a2a116Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `downloadPdf['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const downloadPdf = {
    '/documents/public/surat/{id}/pdf': downloadPdf175f3b2747492debcba34c78c9a49c61,
    '/documents/surat/{id}/pdf': downloadPdfccfc3e9865ca14301019c9d23437884e,
    '/admin/surat/{id}/pdf': downloadPdf05d105208d0811647b427cb9e4a2a116,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
const previewAttachmentDocument8720acd5cf3955db442eddf519381d1b = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, options),
    method: 'get',
})

previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.definition = {
    methods: ["get","head"],
    url: '/documents/public/surat/{id}/attachment-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
    const previewAttachmentDocument8720acd5cf3955db442eddf519381d1bForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
        previewAttachmentDocument8720acd5cf3955db442eddf519381d1bForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/public/surat/{id}/attachment-document'
 */
        previewAttachmentDocument8720acd5cf3955db442eddf519381d1bForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachmentDocument8720acd5cf3955db442eddf519381d1b.form = previewAttachmentDocument8720acd5cf3955db442eddf519381d1bForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
const previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, options),
    method: 'get',
})

previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/attachment-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
    const previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2aForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
        previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2aForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/documents/surat/{id}/attachment-document'
 */
        previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2aForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a.form = previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2aForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
const previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, options),
    method: 'get',
})

previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/attachment-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
    const previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2cForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
        previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2cForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:62
 * @route '/admin/surat/{id}/attachment-document'
 */
        previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2cForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c.form = previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2cForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachmentDocument, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewAttachmentDocument['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewAttachmentDocument = {
    '/documents/public/surat/{id}/attachment-document': previewAttachmentDocument8720acd5cf3955db442eddf519381d1b,
    '/documents/surat/{id}/attachment-document': previewAttachmentDocumentd17613bad81eb08556aea16b946b7e2a,
    '/admin/surat/{id}/attachment-document': previewAttachmentDocumenta84193a3506562ba99b5902c740f9a2c,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
const downloadAttachmentPdf152584be3f1c5011949d323881a2bbee = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, options),
    method: 'get',
})

downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.definition = {
    methods: ["get","head"],
    url: '/documents/public/surat/{id}/attachment-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
    const downloadAttachmentPdf152584be3f1c5011949d323881a2bbeeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdf152584be3f1c5011949d323881a2bbeeForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/public/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdf152584be3f1c5011949d323881a2bbeeForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadAttachmentPdf152584be3f1c5011949d323881a2bbee.form = downloadAttachmentPdf152584be3f1c5011949d323881a2bbeeForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
const downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, options),
    method: 'get',
})

downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/attachment-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
    const downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/documents/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9.form = downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
const downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, options),
    method: 'get',
})

downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/attachment-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
    const downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:70
 * @route '/admin/surat/{id}/attachment-pdf'
 */
        downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9.form = downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::downloadAttachmentPdf, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `downloadAttachmentPdf['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const downloadAttachmentPdf = {
    '/documents/public/surat/{id}/attachment-pdf': downloadAttachmentPdf152584be3f1c5011949d323881a2bbee,
    '/documents/surat/{id}/attachment-pdf': downloadAttachmentPdf68ce1b85ebc195cfeadefdc3dd3cefa9,
    '/admin/surat/{id}/attachment-pdf': downloadAttachmentPdfd1c3972bf199fae0d433df3c03bae5c9,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
const previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, options),
    method: 'get',
})

previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.definition = {
    methods: ["get","head"],
    url: '/documents/public/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
    const previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
        previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/public/lampiran/{id}/preview'
 */
        previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0.form = previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
const previewAttachment4e6e297488f868d2b99e03f1750ec7db = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
    method: 'get',
})

previewAttachment4e6e297488f868d2b99e03f1750ec7db.definition = {
    methods: ["get","head"],
    url: '/documents/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
previewAttachment4e6e297488f868d2b99e03f1750ec7db.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachment4e6e297488f868d2b99e03f1750ec7db.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
previewAttachment4e6e297488f868d2b99e03f1750ec7db.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
previewAttachment4e6e297488f868d2b99e03f1750ec7db.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
    const previewAttachment4e6e297488f868d2b99e03f1750ec7dbForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
        previewAttachment4e6e297488f868d2b99e03f1750ec7dbForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/documents/lampiran/{id}/preview'
 */
        previewAttachment4e6e297488f868d2b99e03f1750ec7dbForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachment4e6e297488f868d2b99e03f1750ec7db.form = previewAttachment4e6e297488f868d2b99e03f1750ec7dbForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
const previewAttachment475f6acc77985b523f13d9d85f732eed = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
    method: 'get',
})

previewAttachment475f6acc77985b523f13d9d85f732eed.definition = {
    methods: ["get","head"],
    url: '/admin/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
previewAttachment475f6acc77985b523f13d9d85f732eed.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachment475f6acc77985b523f13d9d85f732eed.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
previewAttachment475f6acc77985b523f13d9d85f732eed.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
previewAttachment475f6acc77985b523f13d9d85f732eed.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
    const previewAttachment475f6acc77985b523f13d9d85f732eedForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
        previewAttachment475f6acc77985b523f13d9d85f732eedForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:78
 * @route '/admin/lampiran/{id}/preview'
 */
        previewAttachment475f6acc77985b523f13d9d85f732eedForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachment475f6acc77985b523f13d9d85f732eed.form = previewAttachment475f6acc77985b523f13d9d85f732eedForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewAttachment['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewAttachment = {
    '/documents/public/lampiran/{id}/preview': previewAttachment3f4b1de5bd279db0bbc497d4aca5dea0,
    '/documents/lampiran/{id}/preview': previewAttachment4e6e297488f868d2b99e03f1750ec7db,
    '/admin/lampiran/{id}/preview': previewAttachment475f6acc77985b523f13d9d85f732eed,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
const index750aeb224105761400ee952169bd178c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index750aeb224105761400ee952169bd178c.url(options),
    method: 'get',
})

index750aeb224105761400ee952169bd178c.definition = {
    methods: ["get","head"],
    url: '/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
index750aeb224105761400ee952169bd178c.url = (options?: RouteQueryOptions) => {
    return index750aeb224105761400ee952169bd178c.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
index750aeb224105761400ee952169bd178c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index750aeb224105761400ee952169bd178c.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
index750aeb224105761400ee952169bd178c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index750aeb224105761400ee952169bd178c.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
    const index750aeb224105761400ee952169bd178cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index750aeb224105761400ee952169bd178c.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
        index750aeb224105761400ee952169bd178cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index750aeb224105761400ee952169bd178c.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/admin/dashboard'
 */
        index750aeb224105761400ee952169bd178cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index750aeb224105761400ee952169bd178c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index750aeb224105761400ee952169bd178c.form = index750aeb224105761400ee952169bd178cForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
const index602e4ed5841f056b73584b0a141bd50e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index602e4ed5841f056b73584b0a141bd50e.url(options),
    method: 'get',
})

index602e4ed5841f056b73584b0a141bd50e.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
index602e4ed5841f056b73584b0a141bd50e.url = (options?: RouteQueryOptions) => {
    return index602e4ed5841f056b73584b0a141bd50e.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
index602e4ed5841f056b73584b0a141bd50e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index602e4ed5841f056b73584b0a141bd50e.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
index602e4ed5841f056b73584b0a141bd50e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index602e4ed5841f056b73584b0a141bd50e.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
    const index602e4ed5841f056b73584b0a141bd50eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index602e4ed5841f056b73584b0a141bd50e.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
        index602e4ed5841f056b73584b0a141bd50eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index602e4ed5841f056b73584b0a141bd50e.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/kaprodi/admin/dashboard'
 */
        index602e4ed5841f056b73584b0a141bd50eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index602e4ed5841f056b73584b0a141bd50e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index602e4ed5841f056b73584b0a141bd50e.form = index602e4ed5841f056b73584b0a141bd50eForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
const indexce69f561054a160c65713918ce2cca67 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexce69f561054a160c65713918ce2cca67.url(options),
    method: 'get',
})

indexce69f561054a160c65713918ce2cca67.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
indexce69f561054a160c65713918ce2cca67.url = (options?: RouteQueryOptions) => {
    return indexce69f561054a160c65713918ce2cca67.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
indexce69f561054a160c65713918ce2cca67.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexce69f561054a160c65713918ce2cca67.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
indexce69f561054a160c65713918ce2cca67.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexce69f561054a160c65713918ce2cca67.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
    const indexce69f561054a160c65713918ce2cca67Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexce69f561054a160c65713918ce2cca67.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
        indexce69f561054a160c65713918ce2cca67Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexce69f561054a160c65713918ce2cca67.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:23
 * @route '/dekan/admin/dashboard'
 */
        indexce69f561054a160c65713918ce2cca67Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexce69f561054a160c65713918ce2cca67.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexce69f561054a160c65713918ce2cca67.form = indexce69f561054a160c65713918ce2cca67Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/dashboard': index750aeb224105761400ee952169bd178c,
    '/kaprodi/admin/dashboard': index602e4ed5841f056b73584b0a141bd50e,
    '/dekan/admin/dashboard': indexce69f561054a160c65713918ce2cca67,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
const showcaa2593158145898f04ae0567d4630f4 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showcaa2593158145898f04ae0567d4630f4.url(args, options),
    method: 'get',
})

showcaa2593158145898f04ae0567d4630f4.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
showcaa2593158145898f04ae0567d4630f4.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return showcaa2593158145898f04ae0567d4630f4.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
showcaa2593158145898f04ae0567d4630f4.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showcaa2593158145898f04ae0567d4630f4.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
showcaa2593158145898f04ae0567d4630f4.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showcaa2593158145898f04ae0567d4630f4.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
    const showcaa2593158145898f04ae0567d4630f4Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showcaa2593158145898f04ae0567d4630f4.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
        showcaa2593158145898f04ae0567d4630f4Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showcaa2593158145898f04ae0567d4630f4.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/admin/surat/{id}'
 */
        showcaa2593158145898f04ae0567d4630f4Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showcaa2593158145898f04ae0567d4630f4.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showcaa2593158145898f04ae0567d4630f4.form = showcaa2593158145898f04ae0567d4630f4Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
const show927fcee68fb401f2468c1f9e0dccf577 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show927fcee68fb401f2468c1f9e0dccf577.url(args, options),
    method: 'get',
})

show927fcee68fb401f2468c1f9e0dccf577.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
show927fcee68fb401f2468c1f9e0dccf577.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show927fcee68fb401f2468c1f9e0dccf577.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
show927fcee68fb401f2468c1f9e0dccf577.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show927fcee68fb401f2468c1f9e0dccf577.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
show927fcee68fb401f2468c1f9e0dccf577.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show927fcee68fb401f2468c1f9e0dccf577.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
    const show927fcee68fb401f2468c1f9e0dccf577Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show927fcee68fb401f2468c1f9e0dccf577.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
        show927fcee68fb401f2468c1f9e0dccf577Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show927fcee68fb401f2468c1f9e0dccf577.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/kaprodi/admin/surat/{id}'
 */
        show927fcee68fb401f2468c1f9e0dccf577Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show927fcee68fb401f2468c1f9e0dccf577.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show927fcee68fb401f2468c1f9e0dccf577.form = show927fcee68fb401f2468c1f9e0dccf577Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
const show02a2db8a6087a1edeb8a0ca8e2e42db2 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
    method: 'get',
})

show02a2db8a6087a1edeb8a0ca8e2e42db2.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
show02a2db8a6087a1edeb8a0ca8e2e42db2.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show02a2db8a6087a1edeb8a0ca8e2e42db2.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
show02a2db8a6087a1edeb8a0ca8e2e42db2.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
show02a2db8a6087a1edeb8a0ca8e2e42db2.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
    const show02a2db8a6087a1edeb8a0ca8e2e42db2Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
        show02a2db8a6087a1edeb8a0ca8e2e42db2Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:30
 * @route '/dekan/admin/surat/{id}'
 */
        show02a2db8a6087a1edeb8a0ca8e2e42db2Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show02a2db8a6087a1edeb8a0ca8e2e42db2.form = show02a2db8a6087a1edeb8a0ca8e2e42db2Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::show, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `show['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const show = {
    '/admin/surat/{id}': showcaa2593158145898f04ae0567d4630f4,
    '/kaprodi/admin/surat/{id}': show927fcee68fb401f2468c1f9e0dccf577,
    '/dekan/admin/surat/{id}': show02a2db8a6087a1edeb8a0ca8e2e42db2,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/admin/surat/bulk-approve'
 */
const bulkApprove4fe116f770c127cd5071ac3a65269eb7 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove4fe116f770c127cd5071ac3a65269eb7.url(options),
    method: 'post',
})

bulkApprove4fe116f770c127cd5071ac3a65269eb7.definition = {
    methods: ["post"],
    url: '/admin/surat/bulk-approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/admin/surat/bulk-approve'
 */
bulkApprove4fe116f770c127cd5071ac3a65269eb7.url = (options?: RouteQueryOptions) => {
    return bulkApprove4fe116f770c127cd5071ac3a65269eb7.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/admin/surat/bulk-approve'
 */
bulkApprove4fe116f770c127cd5071ac3a65269eb7.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove4fe116f770c127cd5071ac3a65269eb7.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/admin/surat/bulk-approve'
 */
    const bulkApprove4fe116f770c127cd5071ac3a65269eb7Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkApprove4fe116f770c127cd5071ac3a65269eb7.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/admin/surat/bulk-approve'
 */
        bulkApprove4fe116f770c127cd5071ac3a65269eb7Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkApprove4fe116f770c127cd5071ac3a65269eb7.url(options),
            method: 'post',
        })
    
    bulkApprove4fe116f770c127cd5071ac3a65269eb7.form = bulkApprove4fe116f770c127cd5071ac3a65269eb7Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/kaprodi/admin/surat/bulk-approve'
 */
const bulkApprove3a753b74e15092dd611151160e02cd5e = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove3a753b74e15092dd611151160e02cd5e.url(options),
    method: 'post',
})

bulkApprove3a753b74e15092dd611151160e02cd5e.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/bulk-approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/kaprodi/admin/surat/bulk-approve'
 */
bulkApprove3a753b74e15092dd611151160e02cd5e.url = (options?: RouteQueryOptions) => {
    return bulkApprove3a753b74e15092dd611151160e02cd5e.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/kaprodi/admin/surat/bulk-approve'
 */
bulkApprove3a753b74e15092dd611151160e02cd5e.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove3a753b74e15092dd611151160e02cd5e.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/kaprodi/admin/surat/bulk-approve'
 */
    const bulkApprove3a753b74e15092dd611151160e02cd5eForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkApprove3a753b74e15092dd611151160e02cd5e.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/kaprodi/admin/surat/bulk-approve'
 */
        bulkApprove3a753b74e15092dd611151160e02cd5eForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkApprove3a753b74e15092dd611151160e02cd5e.url(options),
            method: 'post',
        })
    
    bulkApprove3a753b74e15092dd611151160e02cd5e.form = bulkApprove3a753b74e15092dd611151160e02cd5eForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/dekan/admin/surat/bulk-approve'
 */
const bulkApprove2662aba274dd3cfaf3c340df0106029f = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove2662aba274dd3cfaf3c340df0106029f.url(options),
    method: 'post',
})

bulkApprove2662aba274dd3cfaf3c340df0106029f.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/bulk-approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/dekan/admin/surat/bulk-approve'
 */
bulkApprove2662aba274dd3cfaf3c340df0106029f.url = (options?: RouteQueryOptions) => {
    return bulkApprove2662aba274dd3cfaf3c340df0106029f.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/dekan/admin/surat/bulk-approve'
 */
bulkApprove2662aba274dd3cfaf3c340df0106029f.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove2662aba274dd3cfaf3c340df0106029f.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/dekan/admin/surat/bulk-approve'
 */
    const bulkApprove2662aba274dd3cfaf3c340df0106029fForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkApprove2662aba274dd3cfaf3c340df0106029f.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:100
 * @route '/dekan/admin/surat/bulk-approve'
 */
        bulkApprove2662aba274dd3cfaf3c340df0106029fForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkApprove2662aba274dd3cfaf3c340df0106029f.url(options),
            method: 'post',
        })
    
    bulkApprove2662aba274dd3cfaf3c340df0106029f.form = bulkApprove2662aba274dd3cfaf3c340df0106029fForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::bulkApprove, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `bulkApprove['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const bulkApprove = {
    '/admin/surat/bulk-approve': bulkApprove4fe116f770c127cd5071ac3a65269eb7,
    '/kaprodi/admin/surat/bulk-approve': bulkApprove3a753b74e15092dd611151160e02cd5e,
    '/dekan/admin/surat/bulk-approve': bulkApprove2662aba274dd3cfaf3c340df0106029f,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/admin/surat/{id}/approve'
 */
const approve16eb56d0276f964abeb59572e2a001a2 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve16eb56d0276f964abeb59572e2a001a2.url(args, options),
    method: 'post',
})

approve16eb56d0276f964abeb59572e2a001a2.definition = {
    methods: ["post"],
    url: '/admin/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/admin/surat/{id}/approve'
 */
approve16eb56d0276f964abeb59572e2a001a2.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return approve16eb56d0276f964abeb59572e2a001a2.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/admin/surat/{id}/approve'
 */
approve16eb56d0276f964abeb59572e2a001a2.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve16eb56d0276f964abeb59572e2a001a2.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/admin/surat/{id}/approve'
 */
    const approve16eb56d0276f964abeb59572e2a001a2Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve16eb56d0276f964abeb59572e2a001a2.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/admin/surat/{id}/approve'
 */
        approve16eb56d0276f964abeb59572e2a001a2Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve16eb56d0276f964abeb59572e2a001a2.url(args, options),
            method: 'post',
        })
    
    approve16eb56d0276f964abeb59572e2a001a2.form = approve16eb56d0276f964abeb59572e2a001a2Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/kaprodi/admin/surat/{id}/approve'
 */
const approve27ee588a20739839eef910dd2189e957 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve27ee588a20739839eef910dd2189e957.url(args, options),
    method: 'post',
})

approve27ee588a20739839eef910dd2189e957.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/kaprodi/admin/surat/{id}/approve'
 */
approve27ee588a20739839eef910dd2189e957.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return approve27ee588a20739839eef910dd2189e957.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/kaprodi/admin/surat/{id}/approve'
 */
approve27ee588a20739839eef910dd2189e957.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve27ee588a20739839eef910dd2189e957.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/kaprodi/admin/surat/{id}/approve'
 */
    const approve27ee588a20739839eef910dd2189e957Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve27ee588a20739839eef910dd2189e957.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/kaprodi/admin/surat/{id}/approve'
 */
        approve27ee588a20739839eef910dd2189e957Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve27ee588a20739839eef910dd2189e957.url(args, options),
            method: 'post',
        })
    
    approve27ee588a20739839eef910dd2189e957.form = approve27ee588a20739839eef910dd2189e957Form
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/dekan/admin/surat/{id}/approve'
 */
const approvea20e3116d12d2583e44b08defd89e10d = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approvea20e3116d12d2583e44b08defd89e10d.url(args, options),
    method: 'post',
})

approvea20e3116d12d2583e44b08defd89e10d.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/dekan/admin/surat/{id}/approve'
 */
approvea20e3116d12d2583e44b08defd89e10d.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return approvea20e3116d12d2583e44b08defd89e10d.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/dekan/admin/surat/{id}/approve'
 */
approvea20e3116d12d2583e44b08defd89e10d.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approvea20e3116d12d2583e44b08defd89e10d.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/dekan/admin/surat/{id}/approve'
 */
    const approvea20e3116d12d2583e44b08defd89e10dForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approvea20e3116d12d2583e44b08defd89e10d.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:92
 * @route '/dekan/admin/surat/{id}/approve'
 */
        approvea20e3116d12d2583e44b08defd89e10dForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approvea20e3116d12d2583e44b08defd89e10d.url(args, options),
            method: 'post',
        })
    
    approvea20e3116d12d2583e44b08defd89e10d.form = approvea20e3116d12d2583e44b08defd89e10dForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::approve, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `approve['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const approve = {
    '/admin/surat/{id}/approve': approve16eb56d0276f964abeb59572e2a001a2,
    '/kaprodi/admin/surat/{id}/approve': approve27ee588a20739839eef910dd2189e957,
    '/dekan/admin/surat/{id}/approve': approvea20e3116d12d2583e44b08defd89e10d,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
const rejectRedirectc79b67a26e419525078af90f9b11470c = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, options),
    method: 'get',
})

rejectRedirectc79b67a26e419525078af90f9b11470c.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/reject',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirectc79b67a26e419525078af90f9b11470c.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return rejectRedirectc79b67a26e419525078af90f9b11470c.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirectc79b67a26e419525078af90f9b11470c.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirectc79b67a26e419525078af90f9b11470c.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
    const rejectRedirectc79b67a26e419525078af90f9b11470cForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
        rejectRedirectc79b67a26e419525078af90f9b11470cForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/admin/surat/{id}/reject'
 */
        rejectRedirectc79b67a26e419525078af90f9b11470cForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirectc79b67a26e419525078af90f9b11470c.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    rejectRedirectc79b67a26e419525078af90f9b11470c.form = rejectRedirectc79b67a26e419525078af90f9b11470cForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
const rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
    method: 'get',
})

rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/{id}/reject',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
    const rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5aForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
        rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5aForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
        rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5aForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a.form = rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5aForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
const rejectRedirect465de78c683dcff8230fca8188657daa = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, options),
    method: 'get',
})

rejectRedirect465de78c683dcff8230fca8188657daa.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/{id}/reject',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
rejectRedirect465de78c683dcff8230fca8188657daa.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return rejectRedirect465de78c683dcff8230fca8188657daa.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
rejectRedirect465de78c683dcff8230fca8188657daa.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
rejectRedirect465de78c683dcff8230fca8188657daa.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
    const rejectRedirect465de78c683dcff8230fca8188657daaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
        rejectRedirect465de78c683dcff8230fca8188657daaForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:130
 * @route '/dekan/admin/surat/{id}/reject'
 */
        rejectRedirect465de78c683dcff8230fca8188657daaForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rejectRedirect465de78c683dcff8230fca8188657daa.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    rejectRedirect465de78c683dcff8230fca8188657daa.form = rejectRedirect465de78c683dcff8230fca8188657daaForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `rejectRedirect['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const rejectRedirect = {
    '/admin/surat/{id}/reject': rejectRedirectc79b67a26e419525078af90f9b11470c,
    '/kaprodi/admin/surat/{id}/reject': rejectRedirect374ba9c59f7d5b307a6e6c4a24a54a5a,
    '/dekan/admin/surat/{id}/reject': rejectRedirect465de78c683dcff8230fca8188657daa,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/admin/surat/{id}/reject'
 */
const rejectc79b67a26e419525078af90f9b11470c = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rejectc79b67a26e419525078af90f9b11470c.url(args, options),
    method: 'post',
})

rejectc79b67a26e419525078af90f9b11470c.definition = {
    methods: ["post"],
    url: '/admin/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/admin/surat/{id}/reject'
 */
rejectc79b67a26e419525078af90f9b11470c.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return rejectc79b67a26e419525078af90f9b11470c.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/admin/surat/{id}/reject'
 */
rejectc79b67a26e419525078af90f9b11470c.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rejectc79b67a26e419525078af90f9b11470c.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/admin/surat/{id}/reject'
 */
    const rejectc79b67a26e419525078af90f9b11470cForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: rejectc79b67a26e419525078af90f9b11470c.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/admin/surat/{id}/reject'
 */
        rejectc79b67a26e419525078af90f9b11470cForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: rejectc79b67a26e419525078af90f9b11470c.url(args, options),
            method: 'post',
        })
    
    rejectc79b67a26e419525078af90f9b11470c.form = rejectc79b67a26e419525078af90f9b11470cForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
const reject374ba9c59f7d5b307a6e6c4a24a54a5a = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
    method: 'post',
})

reject374ba9c59f7d5b307a6e6c4a24a54a5a.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
reject374ba9c59f7d5b307a6e6c4a24a54a5a.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return reject374ba9c59f7d5b307a6e6c4a24a54a5a.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
reject374ba9c59f7d5b307a6e6c4a24a54a5a.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
    const reject374ba9c59f7d5b307a6e6c4a24a54a5aForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/kaprodi/admin/surat/{id}/reject'
 */
        reject374ba9c59f7d5b307a6e6c4a24a54a5aForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject374ba9c59f7d5b307a6e6c4a24a54a5a.url(args, options),
            method: 'post',
        })
    
    reject374ba9c59f7d5b307a6e6c4a24a54a5a.form = reject374ba9c59f7d5b307a6e6c4a24a54a5aForm
    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/dekan/admin/surat/{id}/reject'
 */
const reject465de78c683dcff8230fca8188657daa = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject465de78c683dcff8230fca8188657daa.url(args, options),
    method: 'post',
})

reject465de78c683dcff8230fca8188657daa.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/dekan/admin/surat/{id}/reject'
 */
reject465de78c683dcff8230fca8188657daa.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return reject465de78c683dcff8230fca8188657daa.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/dekan/admin/surat/{id}/reject'
 */
reject465de78c683dcff8230fca8188657daa.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject465de78c683dcff8230fca8188657daa.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/dekan/admin/surat/{id}/reject'
 */
    const reject465de78c683dcff8230fca8188657daaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject465de78c683dcff8230fca8188657daa.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:122
 * @route '/dekan/admin/surat/{id}/reject'
 */
        reject465de78c683dcff8230fca8188657daaForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject465de78c683dcff8230fca8188657daa.url(args, options),
            method: 'post',
        })
    
    reject465de78c683dcff8230fca8188657daa.form = reject465de78c683dcff8230fca8188657daaForm

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::reject, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `reject['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const reject = {
    '/admin/surat/{id}/reject': rejectc79b67a26e419525078af90f9b11470c,
    '/kaprodi/admin/surat/{id}/reject': reject374ba9c59f7d5b307a6e6c4a24a54a5a,
    '/dekan/admin/surat/{id}/reject': reject465de78c683dcff8230fca8188657daa,
}

const DashboardController = { previewTemplate, previewGeneratedDocument, downloadPdf, previewAttachmentDocument, downloadAttachmentPdf, previewAttachment, index, show, bulkApprove, approve, rejectRedirect, reject }

export default DashboardController