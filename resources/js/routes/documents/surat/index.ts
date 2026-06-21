import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
export const templatePreview = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: templatePreview.url(args, options),
    method: 'get',
})

templatePreview.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/template-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
templatePreview.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return templatePreview.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
templatePreview.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
templatePreview.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: templatePreview.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
const templatePreviewForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
templatePreviewForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/documents/surat/{id}/template-preview'
*/
templatePreviewForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: templatePreview.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

templatePreview.form = templatePreviewForm

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
export const generatedDocument = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generatedDocument.url(args, options),
    method: 'get',
})

generatedDocument.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/generated-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
generatedDocument.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generatedDocument.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
generatedDocument.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
generatedDocument.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generatedDocument.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
const generatedDocumentForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
generatedDocumentForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/documents/surat/{id}/generated-document'
*/
generatedDocumentForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generatedDocument.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

generatedDocument.form = generatedDocumentForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
export const generate = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate.url(args, options),
    method: 'get',
})

generate.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generate.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
const generateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generateForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generateForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generate.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

generate.form = generateForm

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
export const pdf = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
pdf.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return pdf.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
pdf.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
pdf.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
const pdfForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
pdfForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/documents/surat/{id}/pdf'
*/
pdfForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

pdf.form = pdfForm

const surat = {
    templatePreview: Object.assign(templatePreview, templatePreview),
    generatedDocument: Object.assign(generatedDocument, generatedDocument),
    generate: Object.assign(generate, generate),
    pdf: Object.assign(pdf, pdf),
}

export default surat