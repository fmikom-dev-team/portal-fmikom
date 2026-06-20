import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
export const showForm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForm.url(options),
    method: 'get',
})

showForm.definition = {
    methods: ["get","head"],
    url: '/verifikasi-qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
showForm.url = (options?: RouteQueryOptions) => {
    return showForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
showForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
showForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showForm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
    const showFormForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showForm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
        showFormForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\QrVerificationController::showForm
 * @see app/Http/Controllers/QrVerificationController.php:24
 * @route '/verifikasi-qr'
 */
        showFormForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showForm.form = showFormForm
/**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
export const verify = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(args, options),
    method: 'get',
})

verify.definition = {
    methods: ["get","head"],
    url: '/verifikasi-qr/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
verify.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    token: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        token: args.token,
                }

    return verify.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
verify.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
verify.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verify.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
    const verifyForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: verify.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
        verifyForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: verify.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\QrVerificationController::verify
 * @see app/Http/Controllers/QrVerificationController.php:30
 * @route '/verifikasi-qr/{token}'
 */
        verifyForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: verify.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
export const image = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: image.url(args, options),
    method: 'get',
})

image.definition = {
    methods: ["get","head"],
    url: '/qr-image/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
image.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    token: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        token: args.token,
                }

    return image.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
image.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: image.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
image.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: image.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
    const imageForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: image.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
        imageForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: image.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\QrVerificationController::image
 * @see app/Http/Controllers/QrVerificationController.php:95
 * @route '/qr-image/{token}'
 */
        imageForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: image.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    image.form = imageForm
const QrVerificationController = { showForm, verify, image }

export default QrVerificationController