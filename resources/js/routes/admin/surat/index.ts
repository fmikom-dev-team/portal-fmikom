import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
*/
export const templatePreview = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: templatePreview.url(args, options),
    method: 'get',
})

templatePreview.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/template-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
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
* @route '/admin/surat/{id}/template-preview'
*/
templatePreview.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
*/
templatePreview.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: templatePreview.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
*/
const templatePreviewForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
*/
templatePreviewForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: templatePreview.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::templatePreview
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:32
* @route '/admin/surat/{id}/template-preview'
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
* @route '/admin/surat/{id}/generated-document'
*/
export const generatedDocument = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generatedDocument.url(args, options),
    method: 'get',
})

generatedDocument.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/generated-document',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/admin/surat/{id}/generated-document'
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
* @route '/admin/surat/{id}/generated-document'
*/
generatedDocument.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/admin/surat/{id}/generated-document'
*/
generatedDocument.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generatedDocument.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/admin/surat/{id}/generated-document'
*/
const generatedDocumentForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/admin/surat/{id}/generated-document'
*/
generatedDocumentForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generatedDocument.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::generatedDocument
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:37
* @route '/admin/surat/{id}/generated-document'
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
* @route '/admin/surat/{id}/generate'
*/
export const generate = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate.url(args, options),
    method: 'get',
})

generate.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
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
* @route '/admin/surat/{id}/generate'
*/
generate.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
generate.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
const generateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
generateForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: generate.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
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
* @route '/admin/surat/{id}/pdf'
*/
export const pdf = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/admin/surat/{id}/pdf'
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
* @route '/admin/surat/{id}/pdf'
*/
pdf.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/admin/surat/{id}/pdf'
*/
pdf.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/admin/surat/{id}/pdf'
*/
const pdfForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/admin/surat/{id}/pdf'
*/
pdfForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::pdf
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:42
* @route '/admin/surat/{id}/pdf'
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

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
* @route '/admin/surat/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
* @route '/admin/surat/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
* @route '/admin/surat/{id}'
*/
const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
* @route '/admin/surat/{id}'
*/
showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::show
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:27
* @route '/admin/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/surat/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:26
* @route '/admin/surat/create'
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
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:55
* @route '/admin/surat/select-type'
*/
export const selectType = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectType.url(options),
    method: 'post',
})

selectType.definition = {
    methods: ["post"],
    url: '/admin/surat/select-type',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:55
* @route '/admin/surat/select-type'
*/
selectType.url = (options?: RouteQueryOptions) => {
    return selectType.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:55
* @route '/admin/surat/select-type'
*/
selectType.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectType.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:55
* @route '/admin/surat/select-type'
*/
const selectTypeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: selectType.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:55
* @route '/admin/surat/select-type'
*/
selectTypeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: selectType.url(options),
    method: 'post',
})

selectType.form = selectTypeForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
export const form = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(args, options),
    method: 'get',
})

form.definition = {
    methods: ["get","head"],
    url: '/admin/surat/form/{jenisSurat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
form.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jenisSurat: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { jenisSurat: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            jenisSurat: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        jenisSurat: typeof args.jenisSurat === 'object'
        ? args.jenisSurat.id
        : args.jenisSurat,
    }

    return form.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
form.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
form.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
const formForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: form.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
formForm.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: form.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
formForm.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: form.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

form.form = formForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
export const previewPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPage.url(options),
    method: 'get',
})

previewPage.definition = {
    methods: ["get","head"],
    url: '/admin/surat/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
previewPage.url = (options?: RouteQueryOptions) => {
    return previewPage.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
previewPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPage.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
previewPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewPage.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
const previewPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewPage.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
previewPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewPage.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:97
* @route '/admin/surat/preview'
*/
previewPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

previewPage.form = previewPageForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:85
* @route '/admin/surat/preview'
*/
export const preview = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview.url(options),
    method: 'post',
})

preview.definition = {
    methods: ["post"],
    url: '/admin/surat/preview',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:85
* @route '/admin/surat/preview'
*/
preview.url = (options?: RouteQueryOptions) => {
    return preview.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:85
* @route '/admin/surat/preview'
*/
preview.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:85
* @route '/admin/surat/preview'
*/
const previewForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: preview.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:85
* @route '/admin/surat/preview'
*/
previewForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: preview.url(options),
    method: 'post',
})

preview.form = previewForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:161
* @route '/admin/surat/store'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/surat/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:161
* @route '/admin/surat/store'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:161
* @route '/admin/surat/store'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:161
* @route '/admin/surat/store'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:161
* @route '/admin/surat/store'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

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

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
edit.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:199
* @route '/admin/surat/{id}/edit'
*/
editForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:237
* @route '/admin/surat/{id}'
*/
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/surat/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:237
* @route '/admin/surat/{id}'
*/
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:237
* @route '/admin/surat/{id}'
*/
update.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:237
* @route '/admin/surat/{id}'
*/
const updateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:237
* @route '/admin/surat/{id}'
*/
updateForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:52
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:52
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:52
* @route '/admin/surat/{id}/approve'
*/
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:52
* @route '/admin/surat/{id}/approve'
*/
const approveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::approve
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:52
* @route '/admin/surat/{id}/approve'
*/
approveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:57
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:57
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
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:57
* @route '/admin/surat/{id}/reject'
*/
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:57
* @route '/admin/surat/{id}/reject'
*/
const rejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::reject
* @see app/Modules/Fast/Controllers/Admin/DashboardController.php:57
* @route '/admin/surat/{id}/reject'
*/
rejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

reject.form = rejectForm

const surat = {
    templatePreview: Object.assign(templatePreview, templatePreview),
    generatedDocument: Object.assign(generatedDocument, generatedDocument),
    generate: Object.assign(generate, generate),
    pdf: Object.assign(pdf, pdf),
    show: Object.assign(show, show),
    create: Object.assign(create, create),
    selectType: Object.assign(selectType, selectType),
    form: Object.assign(form, form),
    previewPage: Object.assign(previewPage, previewPage),
    preview: Object.assign(preview, preview),
    store: Object.assign(store, store),
    index: Object.assign(index, index),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    approve: Object.assign(approve, approve),
    reject: Object.assign(reject, reject),
}

export default surat