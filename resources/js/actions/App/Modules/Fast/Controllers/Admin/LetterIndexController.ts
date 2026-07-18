import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/admin/surat'
*/
const indexb6e06a6546a9d86589b4a3bd0d762019 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb6e06a6546a9d86589b4a3bd0d762019.url(options),
    method: 'get',
})

indexb6e06a6546a9d86589b4a3bd0d762019.definition = {
    methods: ["get","head"],
    url: '/admin/surat',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/admin/surat'
*/
indexb6e06a6546a9d86589b4a3bd0d762019.url = (options?: RouteQueryOptions) => {
    return indexb6e06a6546a9d86589b4a3bd0d762019.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/admin/surat'
*/
indexb6e06a6546a9d86589b4a3bd0d762019.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb6e06a6546a9d86589b4a3bd0d762019.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/admin/surat'
*/
indexb6e06a6546a9d86589b4a3bd0d762019.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexb6e06a6546a9d86589b4a3bd0d762019.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/kaprodi/admin/surat'
*/
const indexa7b3ecf96d6799e3f82b703d51393ef2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa7b3ecf96d6799e3f82b703d51393ef2.url(options),
    method: 'get',
})

indexa7b3ecf96d6799e3f82b703d51393ef2.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/kaprodi/admin/surat'
*/
indexa7b3ecf96d6799e3f82b703d51393ef2.url = (options?: RouteQueryOptions) => {
    return indexa7b3ecf96d6799e3f82b703d51393ef2.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/kaprodi/admin/surat'
*/
indexa7b3ecf96d6799e3f82b703d51393ef2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa7b3ecf96d6799e3f82b703d51393ef2.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/kaprodi/admin/surat'
*/
indexa7b3ecf96d6799e3f82b703d51393ef2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexa7b3ecf96d6799e3f82b703d51393ef2.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/dekan/admin/surat'
*/
const indexd1d46f125ca85668855e663ec8e12f75 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd1d46f125ca85668855e663ec8e12f75.url(options),
    method: 'get',
})

indexd1d46f125ca85668855e663ec8e12f75.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/dekan/admin/surat'
*/
indexd1d46f125ca85668855e663ec8e12f75.url = (options?: RouteQueryOptions) => {
    return indexd1d46f125ca85668855e663ec8e12f75.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/dekan/admin/surat'
*/
indexd1d46f125ca85668855e663ec8e12f75.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd1d46f125ca85668855e663ec8e12f75.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterIndexController::index
* @see app/Modules/Fast/Controllers/Admin/LetterIndexController.php:17
* @route '/dekan/admin/surat'
*/
indexd1d46f125ca85668855e663ec8e12f75.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexd1d46f125ca85668855e663ec8e12f75.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterIndexController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/admin/surat': indexb6e06a6546a9d86589b4a3bd0d762019,
    '/kaprodi/admin/surat': indexa7b3ecf96d6799e3f82b703d51393ef2,
    '/dekan/admin/surat': indexd1d46f125ca85668855e663ec8e12f75,
}

const LetterIndexController = { index }

export default LetterIndexController