import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
* @see \App\Http\Controllers\Api\SuratController::adminValidate
* @see app/Http/Controllers/Api/SuratController.php:111
* @route '/api/fast/surat/{surat}/admin-validation'
*/
export const adminValidate = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adminValidate.url(args, options),
    method: 'post',
})

adminValidate.definition = {
    methods: ["post"],
    url: '/api/fast/surat/{surat}/admin-validation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::adminValidate
* @see app/Http/Controllers/Api/SuratController.php:111
* @route '/api/fast/surat/{surat}/admin-validation'
*/
adminValidate.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return adminValidate.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::adminValidate
* @see app/Http/Controllers/Api/SuratController.php:111
* @route '/api/fast/surat/{surat}/admin-validation'
*/
adminValidate.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adminValidate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\SuratController::approve
* @see app/Http/Controllers/Api/SuratController.php:123
* @route '/api/fast/surat/{surat}/approval'
*/
export const approve = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/api/fast/surat/{surat}/approval',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SuratController::approve
* @see app/Http/Controllers/Api/SuratController.php:123
* @route '/api/fast/surat/{surat}/approval'
*/
approve.url = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return approve.definition.url
            .replace('{surat}', parsedArgs.surat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SuratController::approve
* @see app/Http/Controllers/Api/SuratController.php:123
* @route '/api/fast/surat/{surat}/approval'
*/
approve.post = (args: { surat: number | { id: number } } | [surat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

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

const SuratController = { index, store, show, adminValidate, approve, generateDocument }

export default SuratController