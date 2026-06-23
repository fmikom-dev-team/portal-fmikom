import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
* @route '/admin/categories'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
* @route '/admin/categories'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
* @route '/admin/categories'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::index
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:16
* @route '/admin/categories'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:23
* @route '/admin/categories'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:23
* @route '/admin/categories'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::store
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:23
* @route '/admin/categories'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:40
* @route '/admin/categories/{category}'
*/
export const update = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:40
* @route '/admin/categories/{category}'
*/
update.url = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::update
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:40
* @route '/admin/categories/{category}'
*/
update.put = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:56
* @route '/admin/categories/{category}'
*/
export const destroy = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:56
* @route '/admin/categories/{category}'
*/
destroy.url = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\CategoryController::destroy
* @see app/Modules/Fast/Controllers/Admin/CategoryController.php:56
* @route '/admin/categories/{category}'
*/
destroy.delete = (args: { category: string | number | { id: string | number } } | [category: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const CategoryController = { index, store, update, destroy }

export default CategoryController