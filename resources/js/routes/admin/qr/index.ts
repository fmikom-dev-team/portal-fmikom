import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::index
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:17
* @route '/admin/qr'
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
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::revoke
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:74
* @route '/admin/qr/{id}/revoke'
*/
export const revoke = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: revoke.url(args, options),
    method: 'post',
})

revoke.definition = {
    methods: ["post"],
    url: '/admin/qr/{id}/revoke',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::revoke
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:74
* @route '/admin/qr/{id}/revoke'
*/
revoke.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return revoke.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::revoke
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:74
* @route '/admin/qr/{id}/revoke'
*/
revoke.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: revoke.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::revoke
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:74
* @route '/admin/qr/{id}/revoke'
*/
const revokeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revoke.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\QrManageController::revoke
* @see app/Modules/Fast/Controllers/Admin/QrManageController.php:74
* @route '/admin/qr/{id}/revoke'
*/
revokeForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revoke.url(args, options),
    method: 'post',
})

revoke.form = revokeForm

const qr = {
    index: Object.assign(index, index),
    revoke: Object.assign(revoke, revoke),
}

export default qr