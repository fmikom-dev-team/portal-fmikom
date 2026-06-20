import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/kaprodi/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
export const queue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})

queue.definition = {
    methods: ["get","head"],
    url: '/kaprodi/antrian',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
queue.url = (options?: RouteQueryOptions) => {
    return queue.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
queue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
queue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: queue.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
    const queueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: queue.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
        queueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: queue.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
        queueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: queue.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    queue.form = queueForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/kaprodi/arsip',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
        archiveForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archive.form = archiveForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
export const detail = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

detail.definition = {
    methods: ["get","head"],
    url: '/kaprodi/surat/{id}/detail',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
detail.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return detail.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
detail.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
detail.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: detail.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
    const detailForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: detail.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
        detailForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: detail.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:42
 * @route '/kaprodi/surat/{id}/detail'
 */
        detailForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: detail.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    detail.form = detailForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
 */
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/kaprodi/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
 */
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
 */
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
 */
    const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
 */
        showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:47
 * @route '/kaprodi/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
export const previewAttachment = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})

previewAttachment.definition = {
    methods: ["get","head"],
    url: '/kaprodi/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
previewAttachment.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return previewAttachment.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
previewAttachment.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
previewAttachment.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
    const previewAttachmentForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: previewAttachment.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
        previewAttachmentForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/lampiran/{id}/preview'
 */
        previewAttachmentForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: previewAttachment.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    previewAttachment.form = previewAttachmentForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:57
 * @route '/kaprodi/surat/{id}/approve'
 */
export const approve = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:57
 * @route '/kaprodi/surat/{id}/approve'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:57
 * @route '/kaprodi/surat/{id}/approve'
 */
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:57
 * @route '/kaprodi/surat/{id}/approve'
 */
    const approveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:57
 * @route '/kaprodi/surat/{id}/approve'
 */
        approveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve.url(args, options),
            method: 'post',
        })
    
    approve.form = approveForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:67
 * @route '/kaprodi/surat/{id}/reject'
 */
export const reject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:67
 * @route '/kaprodi/surat/{id}/reject'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:67
 * @route '/kaprodi/surat/{id}/reject'
 */
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:67
 * @route '/kaprodi/surat/{id}/reject'
 */
    const rejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:67
 * @route '/kaprodi/surat/{id}/reject'
 */
        rejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject.url(args, options),
            method: 'post',
        })
    
    reject.form = rejectForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:72
 * @route '/kaprodi/surat/{id}/final-reject'
 */
export const finalReject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

finalReject.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/{id}/final-reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:72
 * @route '/kaprodi/surat/{id}/final-reject'
 */
finalReject.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return finalReject.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:72
 * @route '/kaprodi/surat/{id}/final-reject'
 */
finalReject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:72
 * @route '/kaprodi/surat/{id}/final-reject'
 */
    const finalRejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: finalReject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:72
 * @route '/kaprodi/surat/{id}/final-reject'
 */
        finalRejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: finalReject.url(args, options),
            method: 'post',
        })
    
    finalReject.form = finalRejectForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
 * @route '/kaprodi/surat/{id}/note'
 */
export const saveNote = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

saveNote.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/{id}/note',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
 * @route '/kaprodi/surat/{id}/note'
 */
saveNote.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return saveNote.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
 * @route '/kaprodi/surat/{id}/note'
 */
saveNote.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
 * @route '/kaprodi/surat/{id}/note'
 */
    const saveNoteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: saveNote.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
 * @route '/kaprodi/surat/{id}/note'
 */
        saveNoteForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: saveNote.url(args, options),
            method: 'post',
        })
    
    saveNote.form = saveNoteForm
const ApprovalController = { index, queue, archive, detail, show, previewAttachment, approve, reject, finalReject, saveNote }

export default ApprovalController