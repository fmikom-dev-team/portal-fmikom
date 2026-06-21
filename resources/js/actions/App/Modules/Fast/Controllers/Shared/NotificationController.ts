import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:15
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
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:15
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
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:15
* @route '/notifications/{notificationId}/read'
*/
read.post = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: read.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:15
* @route '/notifications/{notificationId}/read'
*/
const readForm = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: read.url(args, options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::read
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:15
* @route '/notifications/{notificationId}/read'
*/
readForm.post = (args: { notificationId: string | number } | [notificationId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: read.url(args, options),
    method: 'post',
})

read.form = readForm

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:39
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
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:39
* @route '/notifications/read-all'
*/
readAll.url = (options?: RouteQueryOptions) => {
    return readAll.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:39
* @route '/notifications/read-all'
*/
readAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:39
* @route '/notifications/read-all'
*/
const readAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: readAll.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Shared\NotificationController::readAll
* @see app/Modules/Fast/Controllers/Shared/NotificationController.php:39
* @route '/notifications/read-all'
*/
readAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: readAll.url(options),
    method: 'post',
})

readAll.form = readAllForm

const NotificationController = { read, readAll }

export default NotificationController