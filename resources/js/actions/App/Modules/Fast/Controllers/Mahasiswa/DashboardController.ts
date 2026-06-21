import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
const index3f1c8d2b8a2c054e0ec0fbcbc571444d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url(options),
    method: 'get',
})

index3f1c8d2b8a2c054e0ec0fbcbc571444d.definition = {
    methods: ["get","head"],
    url: '/fast/user/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
index3f1c8d2b8a2c054e0ec0fbcbc571444d.url = (options?: RouteQueryOptions) => {
    return index3f1c8d2b8a2c054e0ec0fbcbc571444d.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
index3f1c8d2b8a2c054e0ec0fbcbc571444d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
index3f1c8d2b8a2c054e0ec0fbcbc571444d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
const index3f1c8d2b8a2c054e0ec0fbcbc571444dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
index3f1c8d2b8a2c054e0ec0fbcbc571444dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/fast/user/dashboard'
*/
index3f1c8d2b8a2c054e0ec0fbcbc571444dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index3f1c8d2b8a2c054e0ec0fbcbc571444d.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index3f1c8d2b8a2c054e0ec0fbcbc571444d.form = index3f1c8d2b8a2c054e0ec0fbcbc571444dForm
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
const indexcda560ed7e7d3ddc4bb678606f11ad72 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexcda560ed7e7d3ddc4bb678606f11ad72.url(options),
    method: 'get',
})

indexcda560ed7e7d3ddc4bb678606f11ad72.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
indexcda560ed7e7d3ddc4bb678606f11ad72.url = (options?: RouteQueryOptions) => {
    return indexcda560ed7e7d3ddc4bb678606f11ad72.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
indexcda560ed7e7d3ddc4bb678606f11ad72.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexcda560ed7e7d3ddc4bb678606f11ad72.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
indexcda560ed7e7d3ddc4bb678606f11ad72.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexcda560ed7e7d3ddc4bb678606f11ad72.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
const indexcda560ed7e7d3ddc4bb678606f11ad72Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexcda560ed7e7d3ddc4bb678606f11ad72.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
indexcda560ed7e7d3ddc4bb678606f11ad72Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexcda560ed7e7d3ddc4bb678606f11ad72.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index
* @see app/Modules/Fast/Controllers/Mahasiswa/DashboardController.php:18
* @route '/mahasiswa/dashboard'
*/
indexcda560ed7e7d3ddc4bb678606f11ad72Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexcda560ed7e7d3ddc4bb678606f11ad72.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

indexcda560ed7e7d3ddc4bb678606f11ad72.form = indexcda560ed7e7d3ddc4bb678606f11ad72Form

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\DashboardController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/fast/user/dashboard': index3f1c8d2b8a2c054e0ec0fbcbc571444d,
    '/mahasiswa/dashboard': indexcda560ed7e7d3ddc4bb678606f11ad72,
}

const DashboardController = { index }

export default DashboardController