import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
export const show = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dosen/jenis-surat/{jenisSurat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
show.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jenisSurat: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { jenisSurat: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            jenisSurat: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        jenisSurat: typeof args.jenisSurat === 'object'
        ? args.jenisSurat.id
        : args.jenisSurat,
    }

    return show.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
show.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
show.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
const showForm = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
showForm.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\LetterTypeController::show
* @see app/Modules/Fast/Controllers/Dosen/LetterTypeController.php:13
* @route '/dosen/jenis-surat/{jenisSurat}'
*/
showForm.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const LetterTypeController = { show }

export default LetterTypeController