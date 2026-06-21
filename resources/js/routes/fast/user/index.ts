import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import submissions from './submissions'
import history3c919d from './history'
import surat from './surat'
/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/fast/user',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: index.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: index.url(options),
    method: 'put',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: index.url(options),
    method: 'patch',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: index.url(options),
    method: 'delete',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
index.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: index.url(options),
    method: 'options',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
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
* @route '/fast/user'
*/
indexForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: index.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/fast/user'
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
* @route '/fast/user'
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
* @route '/fast/user'
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
* @route '/fast/user'
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
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/fast/user/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::dashboard
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
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
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
export const ajukan = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

ajukan.definition = {
    methods: ["get","head"],
    url: '/fast/user/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
ajukan.url = (options?: RouteQueryOptions) => {
    return ajukan.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
ajukan.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
ajukan.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ajukan.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
const ajukanForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
ajukanForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ajukan.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::ajukan
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
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
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/fast/user/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
*/
historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\HistoryController::history
* @see app/Modules/Fast/Controllers/Mahasiswa/HistoryController.php:15
* @route '/fast/user/history'
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

const user = {
    index: Object.assign(index, index),
    dashboard: Object.assign(dashboard, dashboard),
    ajukan: Object.assign(ajukan, ajukan),
    submissions: Object.assign(submissions, submissions),
    history: Object.assign(history, history3c919d),
    surat: Object.assign(surat, surat),
}

export default user