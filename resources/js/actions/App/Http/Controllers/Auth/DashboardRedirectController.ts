import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
const DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url = (options?: RouteQueryOptions) => {
    return DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
    const DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
        DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/dashboard'
 */
        DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9.form = DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9Form
    /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
const DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url(options),
    method: 'get',
})

DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.definition = {
    methods: ["get","head"],
    url: '/redirect-dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url = (options?: RouteQueryOptions) => {
    return DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
    const DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
        DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\DashboardRedirectController::__invoke
 * @see app/Http/Controllers/Auth/DashboardRedirectController.php:11
 * @route '/redirect-dashboard'
 */
        DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09.form = DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09Form

/**
* Multiple routes resolve to \App\Http\Controllers\Auth\DashboardRedirectController::DashboardRedirectController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `DashboardRedirectController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const DashboardRedirectController = {
    '/dashboard': DashboardRedirectController42a740574ecbfbac32f8cc353fc32db9,
    '/redirect-dashboard': DashboardRedirectController0d6a7d5b24df154c8ec425b4194bdc09,
}

export default DashboardRedirectController