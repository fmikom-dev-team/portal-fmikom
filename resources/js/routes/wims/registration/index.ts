import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'

/**
* @see \App\Modules\Wims\Controllers\Mahasiswa\RegistrationController::store
* @route '/wims/pendaftaran'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/wims/pendaftaran',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Wims\Controllers\Mahasiswa\RegistrationController::store
* @route '/wims/pendaftaran'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Wims\Controllers\Mahasiswa\RegistrationController::store
* @route '/wims/pendaftaran'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Wims\Controllers\Mahasiswa\RegistrationController::store
* @route '/wims/pendaftaran'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Wims\Controllers\Mahasiswa\RegistrationController::store
* @route '/wims/pendaftaran'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const registration = {
    store: Object.assign(store, store),
}

export default registration
