import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import submissions from './submissions'
import history3c919d from './history'
import surat from './surat'
/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/mahasiswa',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: index.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: index.url(options),
    method: 'put',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: index.url(options),
    method: 'patch',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: index.url(options),
    method: 'delete',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/mahasiswa'
*/
index.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: index.url(options),
    method: 'options',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
export const ajukan = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

ajukan.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
ajukan.url = (options?: RouteQueryOptions) => {
    return ajukan.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
ajukan.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
ajukan.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ajukan.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/mahasiswa/history'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

const mahasiswa = {
    index: Object.assign(index, index),
    dashboard: Object.assign(dashboard, dashboard),
    ajukan: Object.assign(ajukan, ajukan),
    submissions: Object.assign(submissions, submissions),
    history: Object.assign(history, history3c919d),
    surat: Object.assign(surat, surat),
}

export default mahasiswa