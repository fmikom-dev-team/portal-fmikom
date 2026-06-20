import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
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

const QrManageController = { index, revoke }

export default QrManageController