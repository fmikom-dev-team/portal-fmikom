import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import surat from './surat'
import lampiran from './lampiran'
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/kaprodi/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::dashboard
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:22
 * @route '/kaprodi/dashboard'
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
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
export const antrian = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: antrian.url(options),
    method: 'get',
})

antrian.definition = {
    methods: ["get","head"],
    url: '/kaprodi/antrian',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
antrian.url = (options?: RouteQueryOptions) => {
    return antrian.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
antrian.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: antrian.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
antrian.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: antrian.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
    const antrianForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: antrian.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
        antrianForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: antrian.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::antrian
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:27
 * @route '/kaprodi/antrian'
 */
        antrianForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: antrian.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    antrian.form = antrianForm
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
export const arsip = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: arsip.url(options),
    method: 'get',
})

arsip.definition = {
    methods: ["get","head"],
    url: '/kaprodi/arsip',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
arsip.url = (options?: RouteQueryOptions) => {
    return arsip.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
arsip.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: arsip.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
arsip.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: arsip.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
    const arsipForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: arsip.url(options),
        method: 'get',
    })

            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
        arsipForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: arsip.url(options),
            method: 'get',
        })
            /**
* @see \App\Modules\Fast\Controllers\Kaprodi\ApprovalController::arsip
 * @see app/Modules/Fast/Controllers/Kaprodi/ApprovalController.php:32
 * @route '/kaprodi/arsip'
 */
        arsipForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: arsip.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    arsip.form = arsipForm
const kaprodi = {
    dashboard: Object.assign(dashboard, dashboard),
antrian: Object.assign(antrian, antrian),
arsip: Object.assign(arsip, arsip),
surat: Object.assign(surat, surat),
lampiran: Object.assign(lampiran, lampiran),
}

export default kaprodi