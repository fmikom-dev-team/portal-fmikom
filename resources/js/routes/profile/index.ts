import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Modules\Settings\Controllers\ProfileController::edit
* @see app/Modules/Settings/Controllers/ProfileController.php:22
* @route '/settings/profile'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Settings\Controllers\ProfileController::edit
* @see app/Modules/Settings/Controllers/ProfileController.php:22
* @route '/settings/profile'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\ProfileController::edit
* @see app/Modules/Settings/Controllers/ProfileController.php:22
* @route '/settings/profile'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Settings\Controllers\ProfileController::edit
* @see app/Modules/Settings/Controllers/ProfileController.php:22
* @route '/settings/profile'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Settings\Controllers\ProfileController::update
* @see app/Modules/Settings/Controllers/ProfileController.php:35
* @route '/settings/profile'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/settings/profile',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Settings\Controllers\ProfileController::update
* @see app/Modules/Settings/Controllers/ProfileController.php:35
* @route '/settings/profile'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\ProfileController::update
* @see app/Modules/Settings/Controllers/ProfileController.php:35
* @route '/settings/profile'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Modules\Settings\Controllers\ProfileController::destroy
* @see app/Modules/Settings/Controllers/ProfileController.php:86
* @route '/settings/profile'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/settings/profile',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Modules\Settings\Controllers\ProfileController::destroy
* @see app/Modules/Settings/Controllers/ProfileController.php:86
* @route '/settings/profile'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\ProfileController::destroy
* @see app/Modules/Settings/Controllers/ProfileController.php:86
* @route '/settings/profile'
*/
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

/**
* @see \App\Modules\Settings\Controllers\ProfileController::requestDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:111
* @route '/settings/profile/deletion-request'
*/
export const requestDeletion = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDeletion.url(options),
    method: 'post',
})

requestDeletion.definition = {
    methods: ["post"],
    url: '/settings/profile/deletion-request',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Settings\Controllers\ProfileController::requestDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:111
* @route '/settings/profile/deletion-request'
*/
requestDeletion.url = (options?: RouteQueryOptions) => {
    return requestDeletion.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\ProfileController::requestDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:111
* @route '/settings/profile/deletion-request'
*/
requestDeletion.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDeletion.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Settings\Controllers\ProfileController::cancelDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:129
* @route '/settings/profile/deletion-request/cancel'
*/
export const cancelDeletion = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelDeletion.url(options),
    method: 'post',
})

cancelDeletion.definition = {
    methods: ["post"],
    url: '/settings/profile/deletion-request/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Settings\Controllers\ProfileController::cancelDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:129
* @route '/settings/profile/deletion-request/cancel'
*/
cancelDeletion.url = (options?: RouteQueryOptions) => {
    return cancelDeletion.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Settings\Controllers\ProfileController::cancelDeletion
* @see app/Modules/Settings/Controllers/ProfileController.php:129
* @route '/settings/profile/deletion-request/cancel'
*/
cancelDeletion.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelDeletion.url(options),
    method: 'post',
})

const profile = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
    requestDeletion: Object.assign(requestDeletion, requestDeletion),
    cancelDeletion: Object.assign(cancelDeletion, cancelDeletion),
}

export default profile