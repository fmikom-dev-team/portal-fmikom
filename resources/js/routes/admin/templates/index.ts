import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::index
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:21
 * @route '/admin/templates'
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
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:26
 * @route '/admin/templates'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/templates',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:26
 * @route '/admin/templates'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:26
 * @route '/admin/templates'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:26
 * @route '/admin/templates'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::store
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:26
 * @route '/admin/templates'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
export const preview = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/admin/templates/{jenisSurat}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return preview.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
preview.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
    const previewForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: preview.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
        previewForm.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::preview
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:36
 * @route '/admin/templates/{jenisSurat}/preview'
 */
        previewForm.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    preview.form = previewForm
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:51
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
export const duplicate = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate.url(args, options),
    method: 'post',
})

duplicate.definition = {
    methods: ["post"],
    url: '/admin/templates/{jenisSurat}/duplicate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:51
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
duplicate.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return duplicate.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:51
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
duplicate.post = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: duplicate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:51
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
    const duplicateForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: duplicate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::duplicate
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:51
 * @route '/admin/templates/{jenisSurat}/duplicate'
 */
        duplicateForm.post = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: duplicate.url(args, options),
            method: 'post',
        })
    
    duplicate.form = duplicateForm
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:46
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
export const toggleActive = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive.url(args, options),
    method: 'patch',
})

toggleActive.definition = {
    methods: ["patch"],
    url: '/admin/templates/{jenisSurat}/toggle-active',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:46
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleActive.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:46
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
toggleActive.patch = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:46
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
    const toggleActiveForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleActive.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::toggleActive
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:46
 * @route '/admin/templates/{jenisSurat}/toggle-active'
 */
        toggleActiveForm.patch = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleActive.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleActive.form = toggleActiveForm
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:31
 * @route '/admin/templates/{jenisSurat}'
 */
export const update = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:31
 * @route '/admin/templates/{jenisSurat}'
 */
update.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:31
 * @route '/admin/templates/{jenisSurat}'
 */
update.put = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:31
 * @route '/admin/templates/{jenisSurat}'
 */
    const updateForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::update
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:31
 * @route '/admin/templates/{jenisSurat}'
 */
        updateForm.put = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}'
 */
export const destroy = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/templates/{jenisSurat}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}'
 */
destroy.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}'
 */
destroy.delete = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}'
 */
    const destroyForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\TemplateController::destroy
 * @see app/Modules/Fast/Controllers/Admin/TemplateController.php:41
 * @route '/admin/templates/{jenisSurat}'
 */
        destroyForm.delete = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const templates = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
preview: Object.assign(preview, preview),
duplicate: Object.assign(duplicate, duplicate),
toggleActive: Object.assign(toggleActive, toggleActive),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default templates