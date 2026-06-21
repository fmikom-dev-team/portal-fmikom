import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import submissions from './submissions'
import history3c919d from './history'
import surat from './surat'
import jenisSurat from './jenis-surat'
/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/dosen',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: index.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: index.url(options),
    method: 'put',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: index.url(options),
    method: 'patch',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: index.url(options),
    method: 'delete',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
index.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: index.url(options),
    method: 'options',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
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

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: index.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/dosen'
*/
indexForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'OPTIONS',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/dosen/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

dashboard.form = dashboardForm

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
export const ajukan = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

ajukan.definition = {
    methods: ["get","head"],
    url: '/dosen/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
ajukan.url = (options?: RouteQueryOptions) => {
    return ajukan.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
ajukan.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
ajukan.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ajukan.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
const ajukanForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
ajukanForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Dosen/SubmissionController.php:29
* @route '/dosen/ajukan'
*/
ajukanForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ajukan.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ajukan.form = ajukanForm

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/dosen/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::history
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:15
* @route '/dosen/history'
*/
historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

history.form = historyForm

const dosen = {
    index: Object.assign(index, index),
    dashboard: Object.assign(dashboard, dashboard),
    ajukan: Object.assign(ajukan, ajukan),
    submissions: Object.assign(submissions, submissions),
    history: Object.assign(history, history3c919d),
    surat: Object.assign(surat, surat),
    jenisSurat: Object.assign(jenisSurat, jenisSurat),
}

export default dosen