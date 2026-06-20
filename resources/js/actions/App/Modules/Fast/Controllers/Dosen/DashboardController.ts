import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::index
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dosen/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::index
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::index
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\DashboardController::index
* @see app/Modules/Fast/Controllers/Dosen/DashboardController.php:18
* @route '/dosen/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const DashboardController = { index }

export default DashboardController