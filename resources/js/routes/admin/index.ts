import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import surat from './surat'
import lampiran from './lampiran'
import archive from './archive'
import templates from './templates'
import categories from './categories'
import qr from './qr'
import settings from './settings'
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\DashboardController::dashboard
 * @see app/Modules/Fast/Controllers/Admin/DashboardController.php:22
 * @route '/admin/dashboard'
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
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/admin/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
    const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
 */
        historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Admin\HistoryController::history
 * @see app/Modules/Fast/Controllers/Admin/HistoryController.php:14
 * @route '/admin/history'
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
const admin = {
    surat: Object.assign(surat, surat),
lampiran: Object.assign(lampiran, lampiran),
dashboard: Object.assign(dashboard, dashboard),
archive: Object.assign(archive, archive),
history: Object.assign(history, history),
templates: Object.assign(templates, templates),
categories: Object.assign(categories, categories),
qr: Object.assign(qr, qr),
settings: Object.assign(settings, settings),
}

export default admin