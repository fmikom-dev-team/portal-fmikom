import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
const generate4dc0087b29b62374302632a274ce7cf8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'get',
})

generate4dc0087b29b62374302632a274ce7cf8.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate4dc0087b29b62374302632a274ce7cf8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generate4dc0087b29b62374302632a274ce7cf8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate4dc0087b29b62374302632a274ce7cf8.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/documents/surat/{id}/generate'
*/
generate4dc0087b29b62374302632a274ce7cf8.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
const generate03a498acc6b7534dc5a6f2f7299d0045 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'get',
})

generate03a498acc6b7534dc5a6f2f7299d0045.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
generate03a498acc6b7534dc5a6f2f7299d0045.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generate03a498acc6b7534dc5a6f2f7299d0045.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
generate03a498acc6b7534dc5a6f2f7299d0045.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:182
* @route '/admin/surat/{id}/generate'
*/
generate03a498acc6b7534dc5a6f2f7299d0045.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::generate, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `generate['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const generate = {
    '/documents/surat/{id}/generate': generate4dc0087b29b62374302632a274ce7cf8,
    '/admin/surat/{id}/generate': generate03a498acc6b7534dc5a6f2f7299d0045,
}

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
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
export const form = (args: { jenisSurat: string | number | { id: string | number } } | [jenisSurat: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
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
form.url = (args: { jenisSurat: string | number | { id: string | number } } | [jenisSurat: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
form.get = (args: { jenisSurat: string | number | { id: string | number } } | [jenisSurat: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
* @see app/Modules/Fast/Controllers/Admin/LetterController.php:68
* @route '/admin/surat/form/{jenisSurat}'
*/
form.head = (args: { jenisSurat: string | number | { id: string | number } } | [jenisSurat: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form.url(args, options),
    method: 'head',
})

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

const LetterController = { generate, create, selectType, form, previewPage, preview, store, edit, update }

export default LetterController