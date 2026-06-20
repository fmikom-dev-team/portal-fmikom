import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import surat from './surat'
import lampiran from './lampiran'
/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::dashboard
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/dekan/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::dashboard
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::dashboard
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::dashboard
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:22
* @route '/dekan/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::antrian
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
export const antrian = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: antrian.url(options),
    method: 'get',
})

antrian.definition = {
    methods: ["get","head"],
    url: '/dekan/antrian',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::antrian
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
antrian.url = (options?: RouteQueryOptions) => {
    return antrian.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::antrian
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
antrian.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: antrian.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::antrian
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:27
* @route '/dekan/antrian'
*/
antrian.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: antrian.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::arsip
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
export const arsip = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: arsip.url(options),
    method: 'get',
})

arsip.definition = {
    methods: ["get","head"],
    url: '/dekan/arsip',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::arsip
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
arsip.url = (options?: RouteQueryOptions) => {
    return arsip.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::arsip
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
arsip.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: arsip.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dekan\ApprovalController::arsip
* @see app/Modules/Fast/Controllers/Dekan/ApprovalController.php:32
* @route '/dekan/arsip'
*/
arsip.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: arsip.url(options),
    method: 'head',
})

const dekan = {
    dashboard: Object.assign(dashboard, dashboard),
    antrian: Object.assign(antrian, antrian),
    arsip: Object.assign(arsip, arsip),
    surat: Object.assign(surat, surat),
    lampiran: Object.assign(lampiran, lampiran),
}

export default dekan