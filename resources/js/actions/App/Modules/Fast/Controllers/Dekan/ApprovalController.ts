import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dekan/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::index
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
export const queue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})

queue.definition = {
    methods: ["get","head"],
    url: '/dekan/antrian',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
queue.url = (options?: RouteQueryOptions) => {
    return queue.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
queue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: queue.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
queue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: queue.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
const queueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: queue.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
queueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: queue.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::queue
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/dekan/arsip',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: archive.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: archive.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::archive
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
*/
export const detail = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

detail.definition = {
    methods: ["get","head"],
    url: '/dekan/surat/{id}/detail',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
*/
detail.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
*/
detail.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: detail.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
*/
const detailForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: detail.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
*/
detailForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: detail.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::detail
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:42
* @route '/dekan/surat/{id}/detail'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
*/
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dekan/surat/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
*/
const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
*/
showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::show
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:47
* @route '/dekan/surat/{id}'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
*/
export const previewAttachment = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})

previewAttachment.definition = {
    methods: ["get","head"],
    url: '/dekan/lampiran/{id}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
*/
previewAttachment.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
*/
previewAttachment.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAttachment.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
*/
const previewAttachmentForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
*/
previewAttachmentForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::previewAttachment
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:52
* @route '/dekan/lampiran/{id}/preview'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:57
* @route '/dekan/surat/{id}/approve'
*/
export const approve = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/dekan/surat/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:57
* @route '/dekan/surat/{id}/approve'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:57
* @route '/dekan/surat/{id}/approve'
*/
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:57
* @route '/dekan/surat/{id}/approve'
*/
const approveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::approve
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:57
* @route '/dekan/surat/{id}/approve'
*/
approveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:67
* @route '/dekan/surat/{id}/reject'
*/
export const reject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/dekan/surat/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:67
* @route '/dekan/surat/{id}/reject'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:67
* @route '/dekan/surat/{id}/reject'
*/
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:67
* @route '/dekan/surat/{id}/reject'
*/
const rejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::reject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:67
* @route '/dekan/surat/{id}/reject'
*/
rejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

reject.form = rejectForm

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:72
* @route '/dekan/surat/{id}/final-reject'
*/
export const finalReject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

finalReject.definition = {
    methods: ["post"],
    url: '/dekan/surat/{id}/final-reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:72
* @route '/dekan/surat/{id}/final-reject'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:72
* @route '/dekan/surat/{id}/final-reject'
*/
finalReject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalReject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:72
* @route '/dekan/surat/{id}/final-reject'
*/
const finalRejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: finalReject.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::finalReject
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:72
* @route '/dekan/surat/{id}/final-reject'
*/
finalRejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: finalReject.url(args, options),
    method: 'post',
})

finalReject.form = finalRejectForm

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:62
* @route '/dekan/surat/{id}/note'
*/
export const saveNote = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

saveNote.definition = {
    methods: ["post"],
    url: '/dekan/surat/{id}/note',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:62
* @route '/dekan/surat/{id}/note'
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
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:62
* @route '/dekan/surat/{id}/note'
*/
saveNote.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveNote.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:62
* @route '/dekan/surat/{id}/note'
*/
const saveNoteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: saveNote.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::saveNote
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:62
* @route '/dekan/surat/{id}/note'
*/
saveNoteForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: saveNote.url(args, options),
    method: 'post',
})

saveNote.form = saveNoteForm

const ApprovalController = { index, queue, archive, detail, show, previewAttachment, approve, reject, finalReject, saveNote }

export default ApprovalController