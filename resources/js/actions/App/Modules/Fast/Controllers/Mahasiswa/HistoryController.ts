import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
const index87b8947623c11a8e049797a3d2d0f9be = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index87b8947623c11a8e049797a3d2d0f9be.url(options),
    method: 'get',
})

index87b8947623c11a8e049797a3d2d0f9be.definition = {
    methods: ["get","head"],
    url: '/fast/user/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
index87b8947623c11a8e049797a3d2d0f9be.url = (options?: RouteQueryOptions) => {
    return index87b8947623c11a8e049797a3d2d0f9be.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
index87b8947623c11a8e049797a3d2d0f9be.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index87b8947623c11a8e049797a3d2d0f9be.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
index87b8947623c11a8e049797a3d2d0f9be.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index87b8947623c11a8e049797a3d2d0f9be.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
const index5a4c493418e0df1b409be4699ee669dc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index5a4c493418e0df1b409be4699ee669dc.url(options),
    method: 'get',
})

index5a4c493418e0df1b409be4699ee669dc.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
index5a4c493418e0df1b409be4699ee669dc.url = (options?: RouteQueryOptions) => {
    return index5a4c493418e0df1b409be4699ee669dc.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
index5a4c493418e0df1b409be4699ee669dc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index5a4c493418e0df1b409be4699ee669dc.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
index5a4c493418e0df1b409be4699ee669dc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index5a4c493418e0df1b409be4699ee669dc.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/fast/user/history': index87b8947623c11a8e049797a3d2d0f9be,
    '/mahasiswa/history': index5a4c493418e0df1b409be4699ee669dc,
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/fast/user/history/{id}'
*/
const show9da74bc9fc8d8ed90070357bf01c09d8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show9da74bc9fc8d8ed90070357bf01c09d8.url(args, options),
    method: 'get',
})

show9da74bc9fc8d8ed90070357bf01c09d8.definition = {
    methods: ["get","head"],
    url: '/fast/user/history/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/fast/user/history/{id}'
*/
show9da74bc9fc8d8ed90070357bf01c09d8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show9da74bc9fc8d8ed90070357bf01c09d8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/fast/user/history/{id}'
*/
show9da74bc9fc8d8ed90070357bf01c09d8.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show9da74bc9fc8d8ed90070357bf01c09d8.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/fast/user/history/{id}'
*/
show9da74bc9fc8d8ed90070357bf01c09d8.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show9da74bc9fc8d8ed90070357bf01c09d8.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/mahasiswa/history/{id}'
*/
const show26c38b6c2e007262af1ec1ea4b164d97 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show26c38b6c2e007262af1ec1ea4b164d97.url(args, options),
    method: 'get',
})

show26c38b6c2e007262af1ec1ea4b164d97.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/history/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/mahasiswa/history/{id}'
*/
show26c38b6c2e007262af1ec1ea4b164d97.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show26c38b6c2e007262af1ec1ea4b164d97.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/mahasiswa/history/{id}'
*/
show26c38b6c2e007262af1ec1ea4b164d97.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show26c38b6c2e007262af1ec1ea4b164d97.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:52
* @route '/mahasiswa/history/{id}'
*/
show26c38b6c2e007262af1ec1ea4b164d97.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show26c38b6c2e007262af1ec1ea4b164d97.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::show, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `show['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const show = {
    '/fast/user/history/{id}': show9da74bc9fc8d8ed90070357bf01c09d8,
    '/mahasiswa/history/{id}': show26c38b6c2e007262af1ec1ea4b164d97,
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/fast/user/surat/{id}/cancel'
*/
const cancel74bd7e97928c58b44e953de9068b8404 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel74bd7e97928c58b44e953de9068b8404.url(args, options),
    method: 'post',
})

cancel74bd7e97928c58b44e953de9068b8404.definition = {
    methods: ["post"],
    url: '/fast/user/surat/{id}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/fast/user/surat/{id}/cancel'
*/
cancel74bd7e97928c58b44e953de9068b8404.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return cancel74bd7e97928c58b44e953de9068b8404.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/fast/user/surat/{id}/cancel'
*/
cancel74bd7e97928c58b44e953de9068b8404.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel74bd7e97928c58b44e953de9068b8404.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/mahasiswa/surat/{id}/cancel'
*/
const canceldf32884bde61aadf9b6523269bf53f90 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: canceldf32884bde61aadf9b6523269bf53f90.url(args, options),
    method: 'post',
})

canceldf32884bde61aadf9b6523269bf53f90.definition = {
    methods: ["post"],
    url: '/mahasiswa/surat/{id}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/mahasiswa/surat/{id}/cancel'
*/
canceldf32884bde61aadf9b6523269bf53f90.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return canceldf32884bde61aadf9b6523269bf53f90.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:168
* @route '/mahasiswa/surat/{id}/cancel'
*/
canceldf32884bde61aadf9b6523269bf53f90.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: canceldf32884bde61aadf9b6523269bf53f90.url(args, options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::cancel, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `cancel['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const cancel = {
    '/fast/user/surat/{id}/cancel': cancel74bd7e97928c58b44e953de9068b8404,
    '/mahasiswa/surat/{id}/cancel': canceldf32884bde61aadf9b6523269bf53f90,
}

const HistoryController = { index, show, cancel }

export default HistoryController