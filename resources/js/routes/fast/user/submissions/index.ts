import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/fast/user/submissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const submissions = {
    store: Object.assign(store, store),
}

export default submissions