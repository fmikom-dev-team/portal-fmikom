import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
export const form = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(options),
    method: 'get',
})

form.definition = {
    methods: ["get","head"],
    url: '/verifikasi-qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
form.url = (options?: RouteQueryOptions) => {
    return form.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
form.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
form.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
    const formForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: form.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
        formForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: form.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\QrVerificationController::form
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
        formForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: form.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    form.form = formForm