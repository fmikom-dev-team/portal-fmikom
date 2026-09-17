import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/admin/templates'
 */
const indexd5705b7847a1ea5930840f23087f91b5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd5705b7847a1ea5930840f23087f91b5.url(options),
    method: 'get',
})

indexd5705b7847a1ea5930840f23087f91b5.definition = {
    methods: ["get","head"],
    url: '/admin/templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/admin/templates'
 */
indexd5705b7847a1ea5930840f23087f91b5.url = (options?: RouteQueryOptions) => {
    return indexd5705b7847a1ea5930840f23087f91b5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/admin/templates'
 */
indexd5705b7847a1ea5930840f23087f91b5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd5705b7847a1ea5930840f23087f91b5.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/admin/templates'
 */
indexd5705b7847a1ea5930840f23087f91b5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexd5705b7847a1ea5930840f23087f91b5.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/kaprodi/admin/templates'
 */
const index957b1c8b41d8f17421652f676b6684f7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index957b1c8b41d8f17421652f676b6684f7.url(options),
    method: 'get',
})

index957b1c8b41d8f17421652f676b6684f7.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/kaprodi/admin/templates'
 */
index957b1c8b41d8f17421652f676b6684f7.url = (options?: RouteQueryOptions) => {
    return index957b1c8b41d8f17421652f676b6684f7.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/kaprodi/admin/templates'
 */
index957b1c8b41d8f17421652f676b6684f7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index957b1c8b41d8f17421652f676b6684f7.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/kaprodi/admin/templates'
 */
index957b1c8b41d8f17421652f676b6684f7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index957b1c8b41d8f17421652f676b6684f7.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/dekan/admin/templates'
 */
const index8d93e22505e0269c341ad40364c118ce = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8d93e22505e0269c341ad40364c118ce.url(options),
    method: 'get',
})

index8d93e22505e0269c341ad40364c118ce.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/dekan/admin/templates'
 */
index8d93e22505e0269c341ad40364c118ce.url = (options?: RouteQueryOptions) => {
    return index8d93e22505e0269c341ad40364c118ce.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/dekan/admin/templates'
 */
index8d93e22505e0269c341ad40364c118ce.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8d93e22505e0269c341ad40364c118ce.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:20
 * @route '/dekan/admin/templates'
 */
index8d93e22505e0269c341ad40364c118ce.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index8d93e22505e0269c341ad40364c118ce.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/templates': indexd5705b7847a1ea5930840f23087f91b5,
    '/kaprodi/admin/templates': index957b1c8b41d8f17421652f676b6684f7,
    '/dekan/admin/templates': index8d93e22505e0269c341ad40364c118ce,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/admin/templates'
 */
const stored5705b7847a1ea5930840f23087f91b5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stored5705b7847a1ea5930840f23087f91b5.url(options),
    method: 'post',
})

stored5705b7847a1ea5930840f23087f91b5.definition = {
    methods: ["post"],
    url: '/admin/templates',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/admin/templates'
 */
stored5705b7847a1ea5930840f23087f91b5.url = (options?: RouteQueryOptions) => {
    return stored5705b7847a1ea5930840f23087f91b5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/admin/templates'
 */
stored5705b7847a1ea5930840f23087f91b5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stored5705b7847a1ea5930840f23087f91b5.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/kaprodi/admin/templates'
 */
const store957b1c8b41d8f17421652f676b6684f7 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store957b1c8b41d8f17421652f676b6684f7.url(options),
    method: 'post',
})

store957b1c8b41d8f17421652f676b6684f7.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/templates',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/kaprodi/admin/templates'
 */
store957b1c8b41d8f17421652f676b6684f7.url = (options?: RouteQueryOptions) => {
    return store957b1c8b41d8f17421652f676b6684f7.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/kaprodi/admin/templates'
 */
store957b1c8b41d8f17421652f676b6684f7.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store957b1c8b41d8f17421652f676b6684f7.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/dekan/admin/templates'
 */
const store8d93e22505e0269c341ad40364c118ce = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8d93e22505e0269c341ad40364c118ce.url(options),
    method: 'post',
})

store8d93e22505e0269c341ad40364c118ce.definition = {
    methods: ["post"],
    url: '/dekan/admin/templates',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/dekan/admin/templates'
 */
store8d93e22505e0269c341ad40364c118ce.url = (options?: RouteQueryOptions) => {
    return store8d93e22505e0269c341ad40364c118ce.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:27
 * @route '/dekan/admin/templates'
 */
store8d93e22505e0269c341ad40364c118ce.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8d93e22505e0269c341ad40364c118ce.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/admin/templates': stored5705b7847a1ea5930840f23087f91b5,
    '/kaprodi/admin/templates': store957b1c8b41d8f17421652f676b6684f7,
    '/dekan/admin/templates': store8d93e22505e0269c341ad40364c118ce,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}/preview'
 */
const preview1458ea1a8e799b0a95dcddcf76bd62fd = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview1458ea1a8e799b0a95dcddcf76bd62fd.url(args, options),
    method: 'get',
})

preview1458ea1a8e799b0a95dcddcf76bd62fd.definition = {
    methods: ["get","head"],
    url: '/admin/templates/{jenisSurat}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview1458ea1a8e799b0a95dcddcf76bd62fd.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return preview1458ea1a8e799b0a95dcddcf76bd62fd.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview1458ea1a8e799b0a95dcddcf76bd62fd.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview1458ea1a8e799b0a95dcddcf76bd62fd.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview1458ea1a8e799b0a95dcddcf76bd62fd.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview1458ea1a8e799b0a95dcddcf76bd62fd.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/kaprodi/admin/templates/{jenisSurat}/preview'
 */
const preview4fa55105ba4d0389070cc8883e48cbd7 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview4fa55105ba4d0389070cc8883e48cbd7.url(args, options),
    method: 'get',
})

preview4fa55105ba4d0389070cc8883e48cbd7.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/templates/{jenisSurat}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/kaprodi/admin/templates/{jenisSurat}/preview'
 */
preview4fa55105ba4d0389070cc8883e48cbd7.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return preview4fa55105ba4d0389070cc8883e48cbd7.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/kaprodi/admin/templates/{jenisSurat}/preview'
 */
preview4fa55105ba4d0389070cc8883e48cbd7.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview4fa55105ba4d0389070cc8883e48cbd7.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/kaprodi/admin/templates/{jenisSurat}/preview'
 */
preview4fa55105ba4d0389070cc8883e48cbd7.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview4fa55105ba4d0389070cc8883e48cbd7.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/dekan/admin/templates/{jenisSurat}/preview'
 */
const preview6966d51cd43ca6a22a8ec9319baf124f = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview6966d51cd43ca6a22a8ec9319baf124f.url(args, options),
    method: 'get',
})

preview6966d51cd43ca6a22a8ec9319baf124f.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/templates/{jenisSurat}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/dekan/admin/templates/{jenisSurat}/preview'
 */
preview6966d51cd43ca6a22a8ec9319baf124f.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return preview6966d51cd43ca6a22a8ec9319baf124f.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/dekan/admin/templates/{jenisSurat}/preview'
 */
preview6966d51cd43ca6a22a8ec9319baf124f.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview6966d51cd43ca6a22a8ec9319baf124f.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/dekan/admin/templates/{jenisSurat}/preview'
 */
preview6966d51cd43ca6a22a8ec9319baf124f.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview6966d51cd43ca6a22a8ec9319baf124f.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::preview, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `preview['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const preview = {
    '/admin/templates/{jenisSurat}/preview': preview1458ea1a8e799b0a95dcddcf76bd62fd,
    '/kaprodi/admin/templates/{jenisSurat}/preview': preview4fa55105ba4d0389070cc8883e48cbd7,
    '/dekan/admin/templates/{jenisSurat}/preview': preview6966d51cd43ca6a22a8ec9319baf124f,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
const duplicated7008cb62fc8bbec332e944dfd8460bd = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicated7008cb62fc8bbec332e944dfd8460bd.url(args, options),
    method: 'post',
})

duplicated7008cb62fc8bbec332e944dfd8460bd.definition = {
    methods: ["post"],
    url: '/admin/templates/{jenisSurat}/duplicate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
duplicated7008cb62fc8bbec332e944dfd8460bd.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return duplicated7008cb62fc8bbec332e944dfd8460bd.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
duplicated7008cb62fc8bbec332e944dfd8460bd.post = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicated7008cb62fc8bbec332e944dfd8460bd.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/kaprodi/admin/templates/{jenisSurat}/duplicate'
 */
const duplicate1ccd8a00f5f6974c2de1e36645851e0c = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate1ccd8a00f5f6974c2de1e36645851e0c.url(args, options),
    method: 'post',
})

duplicate1ccd8a00f5f6974c2de1e36645851e0c.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/templates/{jenisSurat}/duplicate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/kaprodi/admin/templates/{jenisSurat}/duplicate'
 */
duplicate1ccd8a00f5f6974c2de1e36645851e0c.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return duplicate1ccd8a00f5f6974c2de1e36645851e0c.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/kaprodi/admin/templates/{jenisSurat}/duplicate'
 */
duplicate1ccd8a00f5f6974c2de1e36645851e0c.post = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate1ccd8a00f5f6974c2de1e36645851e0c.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/dekan/admin/templates/{jenisSurat}/duplicate'
 */
const duplicate578f79cf42c6bf57bff1b5009a62783c = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate578f79cf42c6bf57bff1b5009a62783c.url(args, options),
    method: 'post',
})

duplicate578f79cf42c6bf57bff1b5009a62783c.definition = {
    methods: ["post"],
    url: '/dekan/admin/templates/{jenisSurat}/duplicate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/dekan/admin/templates/{jenisSurat}/duplicate'
 */
duplicate578f79cf42c6bf57bff1b5009a62783c.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return duplicate578f79cf42c6bf57bff1b5009a62783c.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:62
 * @route '/dekan/admin/templates/{jenisSurat}/duplicate'
 */
duplicate578f79cf42c6bf57bff1b5009a62783c.post = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate578f79cf42c6bf57bff1b5009a62783c.url(args, options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `duplicate['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const duplicate = {
    '/admin/templates/{jenisSurat}/duplicate': duplicated7008cb62fc8bbec332e944dfd8460bd,
    '/kaprodi/admin/templates/{jenisSurat}/duplicate': duplicate1ccd8a00f5f6974c2de1e36645851e0c,
    '/dekan/admin/templates/{jenisSurat}/duplicate': duplicate578f79cf42c6bf57bff1b5009a62783c,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
const toggleActive24eb9428d4dc6193900a741f68e73a1a = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive24eb9428d4dc6193900a741f68e73a1a.url(args, options),
    method: 'patch',
})

toggleActive24eb9428d4dc6193900a741f68e73a1a.definition = {
    methods: ["patch"],
    url: '/admin/templates/{jenisSurat}/toggle-active',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive24eb9428d4dc6193900a741f68e73a1a.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleActive24eb9428d4dc6193900a741f68e73a1a.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive24eb9428d4dc6193900a741f68e73a1a.patch = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive24eb9428d4dc6193900a741f68e73a1a.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/kaprodi/admin/templates/{jenisSurat}/toggle-active'
 */
const toggleActiveefada02b3e0ad96868cf0f64f4e173a0 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActiveefada02b3e0ad96868cf0f64f4e173a0.url(args, options),
    method: 'patch',
})

toggleActiveefada02b3e0ad96868cf0f64f4e173a0.definition = {
    methods: ["patch"],
    url: '/kaprodi/admin/templates/{jenisSurat}/toggle-active',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/kaprodi/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActiveefada02b3e0ad96868cf0f64f4e173a0.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleActiveefada02b3e0ad96868cf0f64f4e173a0.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/kaprodi/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActiveefada02b3e0ad96868cf0f64f4e173a0.patch = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActiveefada02b3e0ad96868cf0f64f4e173a0.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/dekan/admin/templates/{jenisSurat}/toggle-active'
 */
const toggleActive05c38a44eb5f8a0851cf2c52a0b975ee = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.url(args, options),
    method: 'patch',
})

toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.definition = {
    methods: ["patch"],
    url: '/dekan/admin/templates/{jenisSurat}/toggle-active',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/dekan/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:55
 * @route '/dekan/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.patch = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive05c38a44eb5f8a0851cf2c52a0b975ee.url(args, options),
    method: 'patch',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `toggleActive['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const toggleActive = {
    '/admin/templates/{jenisSurat}/toggle-active': toggleActive24eb9428d4dc6193900a741f68e73a1a,
    '/kaprodi/admin/templates/{jenisSurat}/toggle-active': toggleActiveefada02b3e0ad96868cf0f64f4e173a0,
    '/dekan/admin/templates/{jenisSurat}/toggle-active': toggleActive05c38a44eb5f8a0851cf2c52a0b975ee,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/admin/templates/{jenisSurat}'
 */
const update01f505c1b215cb09c80c65f20012e257 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update01f505c1b215cb09c80c65f20012e257.url(args, options),
    method: 'put',
})

update01f505c1b215cb09c80c65f20012e257.definition = {
    methods: ["put"],
    url: '/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/admin/templates/{jenisSurat}'
 */
update01f505c1b215cb09c80c65f20012e257.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update01f505c1b215cb09c80c65f20012e257.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/admin/templates/{jenisSurat}'
 */
update01f505c1b215cb09c80c65f20012e257.put = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update01f505c1b215cb09c80c65f20012e257.url(args, options),
    method: 'put',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
const updateec4553acf41ea339119e762c715217a1 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateec4553acf41ea339119e762c715217a1.url(args, options),
    method: 'put',
})

updateec4553acf41ea339119e762c715217a1.definition = {
    methods: ["put"],
    url: '/kaprodi/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
updateec4553acf41ea339119e762c715217a1.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateec4553acf41ea339119e762c715217a1.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
updateec4553acf41ea339119e762c715217a1.put = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateec4553acf41ea339119e762c715217a1.url(args, options),
    method: 'put',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
const updatee90c4242e6221d90cc466e8c8b71bc81 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatee90c4242e6221d90cc466e8c8b71bc81.url(args, options),
    method: 'put',
})

updatee90c4242e6221d90cc466e8c8b71bc81.definition = {
    methods: ["put"],
    url: '/dekan/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
updatee90c4242e6221d90cc466e8c8b71bc81.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updatee90c4242e6221d90cc466e8c8b71bc81.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:34
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
updatee90c4242e6221d90cc466e8c8b71bc81.put = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatee90c4242e6221d90cc466e8c8b71bc81.url(args, options),
    method: 'put',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::update, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `update['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const update = {
    '/admin/templates/{jenisSurat}': update01f505c1b215cb09c80c65f20012e257,
    '/kaprodi/admin/templates/{jenisSurat}': updateec4553acf41ea339119e762c715217a1,
    '/dekan/admin/templates/{jenisSurat}': updatee90c4242e6221d90cc466e8c8b71bc81,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/admin/templates/{jenisSurat}'
 */
const destroy01f505c1b215cb09c80c65f20012e257 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy01f505c1b215cb09c80c65f20012e257.url(args, options),
    method: 'delete',
})

destroy01f505c1b215cb09c80c65f20012e257.definition = {
    methods: ["delete"],
    url: '/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/admin/templates/{jenisSurat}'
 */
destroy01f505c1b215cb09c80c65f20012e257.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy01f505c1b215cb09c80c65f20012e257.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/admin/templates/{jenisSurat}'
 */
destroy01f505c1b215cb09c80c65f20012e257.delete = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy01f505c1b215cb09c80c65f20012e257.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
const destroyec4553acf41ea339119e762c715217a1 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyec4553acf41ea339119e762c715217a1.url(args, options),
    method: 'delete',
})

destroyec4553acf41ea339119e762c715217a1.definition = {
    methods: ["delete"],
    url: '/kaprodi/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
destroyec4553acf41ea339119e762c715217a1.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroyec4553acf41ea339119e762c715217a1.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/kaprodi/admin/templates/{jenisSurat}'
 */
destroyec4553acf41ea339119e762c715217a1.delete = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyec4553acf41ea339119e762c715217a1.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
const destroye90c4242e6221d90cc466e8c8b71bc81 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroye90c4242e6221d90cc466e8c8b71bc81.url(args, options),
    method: 'delete',
})

destroye90c4242e6221d90cc466e8c8b71bc81.definition = {
    methods: ["delete"],
    url: '/dekan/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
destroye90c4242e6221d90cc466e8c8b71bc81.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroye90c4242e6221d90cc466e8c8b71bc81.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:48
 * @route '/dekan/admin/templates/{jenisSurat}'
 */
destroye90c4242e6221d90cc466e8c8b71bc81.delete = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroye90c4242e6221d90cc466e8c8b71bc81.url(args, options),
    method: 'delete',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\TemplateController::destroy, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `destroy['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const destroy = {
    '/admin/templates/{jenisSurat}': destroy01f505c1b215cb09c80c65f20012e257,
    '/kaprodi/admin/templates/{jenisSurat}': destroyec4553acf41ea339119e762c715217a1,
    '/dekan/admin/templates/{jenisSurat}': destroye90c4242e6221d90cc466e8c8b71bc81,
}

const TemplateController = { index, store, preview, duplicate, toggleActive, update, destroy }

export default TemplateController