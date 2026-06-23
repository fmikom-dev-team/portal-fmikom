import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/documents/public/surat/{id}/template-preview'
 */
previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/documents/public/surat/{id}/template-preview'
 */
previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplate186871ff0b5e0c1f1e7a1072b8450ee8.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/documents/surat/{id}/template-preview'
 */
previewTemplateee614e014c85387e34677786d92b7354.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/documents/surat/{id}/template-preview'
 */
previewTemplateee614e014c85387e34677786d92b7354.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplateee614e014c85387e34677786d92b7354.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/admin/surat/{id}/template-preview'
 */
previewTemplate5ca38ce12df99acea33c48ceb303e37d.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewTemplate
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:31
 * @route '/admin/surat/{id}/template-preview'
 */
previewTemplate5ca38ce12df99acea33c48ceb303e37d.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewTemplate5ca38ce12df99acea33c48ceb303e37d.url(args, options),
    method: 'head',
})

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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/documents/public/surat/{id}/generated-document'
 */
previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/documents/public/surat/{id}/generated-document'
 */
previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocumenteaeb27015f65a96b31d9ec0f00acbb6a.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/documents/surat/{id}/generated-document'
 */
previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/documents/surat/{id}/generated-document'
 */
previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocument35d5c331c7d7f999cb47e33c0877e067.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/admin/surat/{id}/generated-document'
 */
previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewGeneratedDocument
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:36
 * @route '/admin/surat/{id}/generated-document'
 */
previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewGeneratedDocumentece765c81e171029b99b1ecd76d2d462.url(args, options),
    method: 'head',
})

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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/documents/public/surat/{id}/pdf'
 */
downloadPdf175f3b2747492debcba34c78c9a49c61.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/documents/public/surat/{id}/pdf'
 */
downloadPdf175f3b2747492debcba34c78c9a49c61.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdf175f3b2747492debcba34c78c9a49c61.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/documents/surat/{id}/pdf'
 */
downloadPdfccfc3e9865ca14301019c9d23437884e.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/documents/surat/{id}/pdf'
 */
downloadPdfccfc3e9865ca14301019c9d23437884e.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdfccfc3e9865ca14301019c9d23437884e.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/admin/surat/{id}/pdf'
 */
downloadPdf05d105208d0811647b427cb9e4a2a116.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::downloadPdf
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:41
 * @route '/admin/surat/{id}/pdf'
 */
downloadPdf05d105208d0811647b427cb9e4a2a116.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdf05d105208d0811647b427cb9e4a2a116.url(args, options),
    method: 'head',
})

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
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/lampiran/{id}/preview'
 */
previewAttachment4e6e297488f868d2b99e03f1750ec7db.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/documents/lampiran/{id}/preview'
 */
previewAttachment4e6e297488f868d2b99e03f1750ec7db.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment4e6e297488f868d2b99e03f1750ec7db.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
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
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/lampiran/{id}/preview'
 */
previewAttachment475f6acc77985b523f13d9d85f732eed.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:46
 * @route '/admin/lampiran/{id}/preview'
 */
previewAttachment475f6acc77985b523f13d9d85f732eed.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment475f6acc77985b523f13d9d85f732eed.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\DashboardController::previewAttachment, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewAttachment['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewAttachment = {
    '/documents/lampiran/{id}/preview': previewAttachment4e6e297488f868d2b99e03f1750ec7db,
    '/admin/lampiran/{id}/preview': previewAttachment475f6acc77985b523f13d9d85f732eed,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:21
 * @route '/admin/dashboard'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:21
 * @route '/admin/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:21
 * @route '/admin/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::index
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:21
 * @route '/admin/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:26
 * @route '/admin/surat/{id}'
 */
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:26
 * @route '/admin/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:26
 * @route '/admin/surat/{id}'
 */
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:26
 * @route '/admin/surat/{id}'
 */
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:51
 * @route '/admin/surat/{id}/approve'
 */
export const approve = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/admin/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:51
 * @route '/admin/surat/{id}/approve'
 */
approve.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return approve.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:51
 * @route '/admin/surat/{id}/approve'
 */
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:61
 * @route '/admin/surat/{id}/reject'
 */
export const rejectRedirect = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect.url(args, options),
    method: 'get',
})

rejectRedirect.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/reject',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:61
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirect.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return rejectRedirect.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:61
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirect.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rejectRedirect.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::rejectRedirect
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:61
 * @route '/admin/surat/{id}/reject'
 */
rejectRedirect.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: rejectRedirect.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:56
 * @route '/admin/surat/{id}/reject'
 */
export const reject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/admin/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:56
 * @route '/admin/surat/{id}/reject'
 */
reject.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return reject.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:56
 * @route '/admin/surat/{id}/reject'
 */
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})
const DashboardController = { previewTemplate, previewGeneratedDocument, downloadPdf, previewAttachment, index, show, approve, rejectRedirect, reject }

export default DashboardController