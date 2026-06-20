import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::note
* @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
* @route '/kaprodi/surat/{id}/note'
*/
export const note = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: note.url(args, options),
    method: 'post',
})

note.definition = {
    methods: ["post"],
    url: '/kaprodi/surat/{id}/note',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::note
* @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
* @route '/kaprodi/surat/{id}/note'
*/
note.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return note.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::note
* @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:62
* @route '/kaprodi/surat/{id}/note'
*/
note.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: note.url(args, options),
    method: 'post',
})

const surat = {
    detail: Object.assign(detail, detail),
    show: Object.assign(show, show),
    approve: Object.assign(approve, approve),
    reject: Object.assign(reject, reject),
    finalReject: Object.assign(finalReject, finalReject),
    note: Object.assign(note, note),
}

export default surat