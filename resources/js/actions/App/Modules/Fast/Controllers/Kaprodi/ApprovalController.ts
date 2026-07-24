import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:24
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:24
 * @route '/kaprodi/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:24
 * @route '/kaprodi/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::index
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:24
 * @route '/kaprodi/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:31
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:31
 * @route '/kaprodi/antrian'
 */
queue.url = (options?: RouteQueryOptions) => {
    return queue.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:31
 * @route '/kaprodi/antrian'
 */
queue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::queue
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:31
 * @route '/kaprodi/antrian'
 */
queue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: queue.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:38
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:38
 * @route '/kaprodi/arsip'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:38
 * @route '/kaprodi/arsip'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::archive
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:38
 * @route '/kaprodi/arsip'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/surat/{id}/detail'
 */
detail.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::detail
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:52
 * @route '/kaprodi/surat/{id}/detail'
 */
detail.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: detail.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:60
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:60
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:60
 * @route '/kaprodi/surat/{id}'
 */
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::show
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:60
 * @route '/kaprodi/surat/{id}'
 */
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:68
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:68
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:68
 * @route '/kaprodi/lampiran/{id}/preview'
 */
previewAttachment.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::previewAttachment
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:68
 * @route '/kaprodi/lampiran/{id}/preview'
 */
previewAttachment.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::approve
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:82
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:82
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:82
 * @route '/kaprodi/surat/{id}/approve'
 */
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::bulkApprove
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:90
 * @route '/kaprodi/surat/bulk-approve'
 */
export const bulkApprove = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove.url(options),
    method: 'post',
})

bulkApprove.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/bulk-approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::bulkApprove
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:90
 * @route '/kaprodi/surat/bulk-approve'
 */
bulkApprove.url = (options?: RouteQueryOptions) => {
    return bulkApprove.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::bulkApprove
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:90
 * @route '/kaprodi/surat/bulk-approve'
 */
bulkApprove.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::reject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:120
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:120
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:120
 * @route '/kaprodi/surat/{id}/reject'
 */
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::finalReject
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:128
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:128
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:128
 * @route '/kaprodi/surat/{id}/final-reject'
 */
finalReject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::saveNote
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:112
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:112
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
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:112
 * @route '/kaprodi/surat/{id}/note'
 */
saveNote.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})
const ApprovalController = { index, queue, archive, detail, show, previewAttachment, approve, bulkApprove, reject, finalReject, saveNote }

export default ApprovalController