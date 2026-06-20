import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::index
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:22
* @route '/approval/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/approval/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::index
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:22
* @route '/approval/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::index
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:22
* @route '/approval/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::index
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:22
* @route '/approval/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:27
* @route '/approval/antrian'
*/
export const queue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})

queue.definition = {
    methods: ["get","head"],
    url: '/approval/antrian',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:27
* @route '/approval/antrian'
*/
queue.url = (options?: RouteQueryOptions) => {
    return queue.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:27
* @route '/approval/antrian'
*/
queue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:27
* @route '/approval/antrian'
*/
queue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: queue.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:32
* @route '/approval/arsip'
*/
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/approval/arsip',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:32
* @route '/approval/arsip'
*/
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:32
* @route '/approval/arsip'
*/
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:32
* @route '/approval/arsip'
*/
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:42
* @route '/approval/surat/{id}/detail'
*/
export const detail = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

detail.definition = {
    methods: ["get","head"],
    url: '/approval/surat/{id}/detail',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:42
* @route '/approval/surat/{id}/detail'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:42
* @route '/approval/surat/{id}/detail'
*/
detail.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:42
* @route '/approval/surat/{id}/detail'
*/
detail.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: detail.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::show
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:47
* @route '/approval/surat/{id}'
*/
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/approval/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::show
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:47
* @route '/approval/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::show
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:47
* @route '/approval/surat/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::show
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:47
* @route '/approval/surat/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:52
* @route '/approval/lampiran/{id}/preview'
*/
export const previewAttachment = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})

previewAttachment.definition = {
    methods: ["get","head"],
    url: '/approval/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:52
* @route '/approval/lampiran/{id}/preview'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:52
* @route '/approval/lampiran/{id}/preview'
*/
previewAttachment.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:52
* @route '/approval/lampiran/{id}/preview'
*/
previewAttachment.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:57
* @route '/approval/surat/{id}/approve'
*/
export const approve = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/approval/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:57
* @route '/approval/surat/{id}/approve'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:57
* @route '/approval/surat/{id}/approve'
*/
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:67
* @route '/approval/surat/{id}/reject'
*/
export const reject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/approval/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:67
* @route '/approval/surat/{id}/reject'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:67
* @route '/approval/surat/{id}/reject'
*/
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:72
* @route '/approval/surat/{id}/final-reject'
*/
export const finalReject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

finalReject.definition = {
    methods: ["post"],
    url: '/approval/surat/{id}/final-reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:72
* @route '/approval/surat/{id}/final-reject'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:72
* @route '/approval/surat/{id}/final-reject'
*/
finalReject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:62
* @route '/approval/surat/{id}/note'
*/
export const saveNote = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

saveNote.definition = {
    methods: ["post"],
    url: '/approval/surat/{id}/note',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:62
* @route '/approval/surat/{id}/note'
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
* @see \App\Modules\Fast\Controllers\Shared\Approval\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Shared/Approval/ApprovalController.php:62
* @route '/approval/surat/{id}/note'
*/
saveNote.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

const ApprovalController = { index, queue, archive, detail, show, previewAttachment, approve, reject, finalReject, saveNote }

export default ApprovalController