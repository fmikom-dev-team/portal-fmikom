import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController::store
* @see app/Modules/WorkOs/Controllers/Auth/TwoFactorChallengeController.php:26
* @route '/two-factor-challenge'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/two-factor-challenge',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController::store
* @see app/Modules/WorkOs/Controllers/Auth/TwoFactorChallengeController.php:26
* @route '/two-factor-challenge'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController::store
* @see app/Modules/WorkOs/Controllers/Auth/TwoFactorChallengeController.php:26
* @route '/two-factor-challenge'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const login = {
    store: Object.assign(store, store),
}

export default login