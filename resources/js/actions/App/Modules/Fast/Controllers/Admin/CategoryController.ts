import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/admin/categories'
 */
const index377c0d45be70873e0ecaf350cc14e1cf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index377c0d45be70873e0ecaf350cc14e1cf.url(options),
    method: 'get',
})

index377c0d45be70873e0ecaf350cc14e1cf.definition = {
    methods: ["get","head"],
    url: '/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/admin/categories'
 */
index377c0d45be70873e0ecaf350cc14e1cf.url = (options?: RouteQueryOptions) => {
    return index377c0d45be70873e0ecaf350cc14e1cf.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/admin/categories'
 */
index377c0d45be70873e0ecaf350cc14e1cf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index377c0d45be70873e0ecaf350cc14e1cf.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/admin/categories'
 */
index377c0d45be70873e0ecaf350cc14e1cf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index377c0d45be70873e0ecaf350cc14e1cf.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/kaprodi/admin/categories'
 */
const index8b07aa3b1c2796b83a14462ab0656d15 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8b07aa3b1c2796b83a14462ab0656d15.url(options),
    method: 'get',
})

index8b07aa3b1c2796b83a14462ab0656d15.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/kaprodi/admin/categories'
 */
index8b07aa3b1c2796b83a14462ab0656d15.url = (options?: RouteQueryOptions) => {
    return index8b07aa3b1c2796b83a14462ab0656d15.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/kaprodi/admin/categories'
 */
index8b07aa3b1c2796b83a14462ab0656d15.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8b07aa3b1c2796b83a14462ab0656d15.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/kaprodi/admin/categories'
 */
index8b07aa3b1c2796b83a14462ab0656d15.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index8b07aa3b1c2796b83a14462ab0656d15.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/dekan/admin/categories'
 */
const indexab103ac7bafaf67a1bab6c43d4f96ad8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexab103ac7bafaf67a1bab6c43d4f96ad8.url(options),
    method: 'get',
})

indexab103ac7bafaf67a1bab6c43d4f96ad8.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/dekan/admin/categories'
 */
indexab103ac7bafaf67a1bab6c43d4f96ad8.url = (options?: RouteQueryOptions) => {
    return indexab103ac7bafaf67a1bab6c43d4f96ad8.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/dekan/admin/categories'
 */
indexab103ac7bafaf67a1bab6c43d4f96ad8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexab103ac7bafaf67a1bab6c43d4f96ad8.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
 * @route '/dekan/admin/categories'
 */
indexab103ac7bafaf67a1bab6c43d4f96ad8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexab103ac7bafaf67a1bab6c43d4f96ad8.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\CategoryController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/categories': index377c0d45be70873e0ecaf350cc14e1cf,
    '/kaprodi/admin/categories': index8b07aa3b1c2796b83a14462ab0656d15,
    '/dekan/admin/categories': indexab103ac7bafaf67a1bab6c43d4f96ad8,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/admin/categories'
 */
const store377c0d45be70873e0ecaf350cc14e1cf = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store377c0d45be70873e0ecaf350cc14e1cf.url(options),
    method: 'post',
})

store377c0d45be70873e0ecaf350cc14e1cf.definition = {
    methods: ["post"],
    url: '/admin/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/admin/categories'
 */
store377c0d45be70873e0ecaf350cc14e1cf.url = (options?: RouteQueryOptions) => {
    return store377c0d45be70873e0ecaf350cc14e1cf.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/admin/categories'
 */
store377c0d45be70873e0ecaf350cc14e1cf.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store377c0d45be70873e0ecaf350cc14e1cf.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/kaprodi/admin/categories'
 */
const store8b07aa3b1c2796b83a14462ab0656d15 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8b07aa3b1c2796b83a14462ab0656d15.url(options),
    method: 'post',
})

store8b07aa3b1c2796b83a14462ab0656d15.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/kaprodi/admin/categories'
 */
store8b07aa3b1c2796b83a14462ab0656d15.url = (options?: RouteQueryOptions) => {
    return store8b07aa3b1c2796b83a14462ab0656d15.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/kaprodi/admin/categories'
 */
store8b07aa3b1c2796b83a14462ab0656d15.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8b07aa3b1c2796b83a14462ab0656d15.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/dekan/admin/categories'
 */
const storeab103ac7bafaf67a1bab6c43d4f96ad8 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeab103ac7bafaf67a1bab6c43d4f96ad8.url(options),
    method: 'post',
})

storeab103ac7bafaf67a1bab6c43d4f96ad8.definition = {
    methods: ["post"],
    url: '/dekan/admin/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/dekan/admin/categories'
 */
storeab103ac7bafaf67a1bab6c43d4f96ad8.url = (options?: RouteQueryOptions) => {
    return storeab103ac7bafaf67a1bab6c43d4f96ad8.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:25
 * @route '/dekan/admin/categories'
 */
storeab103ac7bafaf67a1bab6c43d4f96ad8.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeab103ac7bafaf67a1bab6c43d4f96ad8.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\CategoryController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/admin/categories': store377c0d45be70873e0ecaf350cc14e1cf,
    '/kaprodi/admin/categories': store8b07aa3b1c2796b83a14462ab0656d15,
    '/dekan/admin/categories': storeab103ac7bafaf67a1bab6c43d4f96ad8,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/admin/categories/{category}'
 */
const updated3574d41649b4dd208d031db42e0efd4 = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updated3574d41649b4dd208d031db42e0efd4.url(args, options),
    method: 'put',
})

updated3574d41649b4dd208d031db42e0efd4.definition = {
    methods: ["put"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/admin/categories/{category}'
 */
updated3574d41649b4dd208d031db42e0efd4.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return updated3574d41649b4dd208d031db42e0efd4.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/admin/categories/{category}'
 */
updated3574d41649b4dd208d031db42e0efd4.put = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updated3574d41649b4dd208d031db42e0efd4.url(args, options),
    method: 'put',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/kaprodi/admin/categories/{category}'
 */
const update017c645f2893a21c57fb24ddf26dbd68 = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update017c645f2893a21c57fb24ddf26dbd68.url(args, options),
    method: 'put',
})

update017c645f2893a21c57fb24ddf26dbd68.definition = {
    methods: ["put"],
    url: '/kaprodi/admin/categories/{category}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/kaprodi/admin/categories/{category}'
 */
update017c645f2893a21c57fb24ddf26dbd68.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return update017c645f2893a21c57fb24ddf26dbd68.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/kaprodi/admin/categories/{category}'
 */
update017c645f2893a21c57fb24ddf26dbd68.put = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update017c645f2893a21c57fb24ddf26dbd68.url(args, options),
    method: 'put',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/dekan/admin/categories/{category}'
 */
const update77b15b03af456df615b1604b47e0af3b = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update77b15b03af456df615b1604b47e0af3b.url(args, options),
    method: 'put',
})

update77b15b03af456df615b1604b47e0af3b.definition = {
    methods: ["put"],
    url: '/dekan/admin/categories/{category}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/dekan/admin/categories/{category}'
 */
update77b15b03af456df615b1604b47e0af3b.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return update77b15b03af456df615b1604b47e0af3b.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:43
 * @route '/dekan/admin/categories/{category}'
 */
update77b15b03af456df615b1604b47e0af3b.put = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update77b15b03af456df615b1604b47e0af3b.url(args, options),
    method: 'put',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\CategoryController::update, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `update['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const update = {
    '/admin/categories/{category}': updated3574d41649b4dd208d031db42e0efd4,
    '/kaprodi/admin/categories/{category}': update017c645f2893a21c57fb24ddf26dbd68,
    '/dekan/admin/categories/{category}': update77b15b03af456df615b1604b47e0af3b,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/admin/categories/{category}'
 */
const destroyd3574d41649b4dd208d031db42e0efd4 = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyd3574d41649b4dd208d031db42e0efd4.url(args, options),
    method: 'delete',
})

destroyd3574d41649b4dd208d031db42e0efd4.definition = {
    methods: ["delete"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/admin/categories/{category}'
 */
destroyd3574d41649b4dd208d031db42e0efd4.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroyd3574d41649b4dd208d031db42e0efd4.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/admin/categories/{category}'
 */
destroyd3574d41649b4dd208d031db42e0efd4.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyd3574d41649b4dd208d031db42e0efd4.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/kaprodi/admin/categories/{category}'
 */
const destroy017c645f2893a21c57fb24ddf26dbd68 = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy017c645f2893a21c57fb24ddf26dbd68.url(args, options),
    method: 'delete',
})

destroy017c645f2893a21c57fb24ddf26dbd68.definition = {
    methods: ["delete"],
    url: '/kaprodi/admin/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/kaprodi/admin/categories/{category}'
 */
destroy017c645f2893a21c57fb24ddf26dbd68.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroy017c645f2893a21c57fb24ddf26dbd68.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/kaprodi/admin/categories/{category}'
 */
destroy017c645f2893a21c57fb24ddf26dbd68.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy017c645f2893a21c57fb24ddf26dbd68.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/dekan/admin/categories/{category}'
 */
const destroy77b15b03af456df615b1604b47e0af3b = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy77b15b03af456df615b1604b47e0af3b.url(args, options),
    method: 'delete',
})

destroy77b15b03af456df615b1604b47e0af3b.definition = {
    methods: ["delete"],
    url: '/dekan/admin/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/dekan/admin/categories/{category}'
 */
destroy77b15b03af456df615b1604b47e0af3b.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroy77b15b03af456df615b1604b47e0af3b.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
 * @see app/Modules/Fast/Controllers/Admin/CategoryController.php:60
 * @route '/dekan/admin/categories/{category}'
 */
destroy77b15b03af456df615b1604b47e0af3b.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy77b15b03af456df615b1604b47e0af3b.url(args, options),
    method: 'delete',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\CategoryController::destroy, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `destroy['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const destroy = {
    '/admin/categories/{category}': destroyd3574d41649b4dd208d031db42e0efd4,
    '/kaprodi/admin/categories/{category}': destroy017c645f2893a21c57fb24ddf26dbd68,
    '/dekan/admin/categories/{category}': destroy77b15b03af456df615b1604b47e0af3b,
}

const CategoryController = { index, store, update, destroy }

export default CategoryController