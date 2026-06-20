import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
* @route '/dosen/history/{id}'
*/
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dosen/history/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
* @route '/dosen/history/{id}'
*/
show.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
* @route '/dosen/history/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Dosen\HistoryController::show
* @see app/Modules/Fast/Controllers/Dosen/HistoryController.php:52
* @route '/dosen/history/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

const history = {
    show: Object.assign(show, show),
}

export default history