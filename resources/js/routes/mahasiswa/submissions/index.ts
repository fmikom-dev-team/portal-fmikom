import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
 * @route '/mahasiswa/submissions'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/mahasiswa/submissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
 * @route '/mahasiswa/submissions'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
 * @route '/mahasiswa/submissions'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
 * @route '/mahasiswa/submissions'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
 * @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
 * @route '/mahasiswa/submissions'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const submissions = {
    store: Object.assign(store, store),
}

export default submissions