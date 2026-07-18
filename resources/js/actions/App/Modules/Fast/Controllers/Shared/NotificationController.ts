import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:22
* @route '/notifications/{notificationId}/read'
*/
export const read = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: read.url(args, options),
    method: 'post',
})

read.definition = {
    methods: ["post"],
    url: '/notifications/{notificationId}/read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:22
* @route '/notifications/{notificationId}/read'
*/
read.url = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notificationId: args }
    }

    if (Array.isArray(args)) {
        args = {
            notificationId: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        notificationId: args.notificationId,
    }

    return read.definition.url
            .replace('{notificationId}', parsedArgs.notificationId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:22
* @route '/notifications/{notificationId}/read'
*/
read.post = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: read.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:50
* @route '/notifications/read-all'
*/
export const readAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

readAll.definition = {
    methods: ["post"],
    url: '/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:50
* @route '/notifications/read-all'
*/
readAll.url = (options?: RouteQueryOptions) => {
    return readAll.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:50
* @route '/notifications/read-all'
*/
readAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

const NotificationController = { read, readAll }

export default NotificationController