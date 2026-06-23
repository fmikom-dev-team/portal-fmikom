import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/fast/user/ajukan'
*/
const create92801d7540bdfe071e00b64abeede393 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'get',
})

create92801d7540bdfe071e00b64abeede393.definition = {
    methods: ["get","head"],
    url: '/fast/user/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.url = (options?: RouteQueryOptions) => {
    return create92801d7540bdfe071e00b64abeede393.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/mahasiswa/ajukan'
*/
const create02d423413b9e77609d83f7fc6e747101 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'get',
})

create02d423413b9e77609d83f7fc6e747101.definition = {
    methods: ["get","head"],
    url: '/mahasiswa/ajukan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.url = (options?: RouteQueryOptions) => {
    return create02d423413b9e77609d83f7fc6e747101.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:28
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `create['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const create = {
    '/fast/user/ajukan': create92801d7540bdfe071e00b64abeede393,
    '/mahasiswa/ajukan': create02d423413b9e77609d83f7fc6e747101,
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/fast/user/submissions'
*/
const storefa8f1b7ec6e51ab02156722b8385a8d5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storefa8f1b7ec6e51ab02156722b8385a8d5.url(options),
    method: 'post',
})

storefa8f1b7ec6e51ab02156722b8385a8d5.definition = {
    methods: ["post"],
    url: '/fast/user/submissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/fast/user/submissions'
*/
storefa8f1b7ec6e51ab02156722b8385a8d5.url = (options?: RouteQueryOptions) => {
    return storefa8f1b7ec6e51ab02156722b8385a8d5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/fast/user/submissions'
*/
storefa8f1b7ec6e51ab02156722b8385a8d5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storefa8f1b7ec6e51ab02156722b8385a8d5.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/mahasiswa/submissions'
*/
const storea3ef875fdb8283f578a11a3e7e42104d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storea3ef875fdb8283f578a11a3e7e42104d.url(options),
    method: 'post',
})

storea3ef875fdb8283f578a11a3e7e42104d.definition = {
    methods: ["post"],
    url: '/mahasiswa/submissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/mahasiswa/submissions'
*/
storea3ef875fdb8283f578a11a3e7e42104d.url = (options?: RouteQueryOptions) => {
    return storea3ef875fdb8283f578a11a3e7e42104d.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:120
* @route '/mahasiswa/submissions'
*/
storea3ef875fdb8283f578a11a3e7e42104d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storea3ef875fdb8283f578a11a3e7e42104d.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/fast/user/submissions': storefa8f1b7ec6e51ab02156722b8385a8d5,
    '/mahasiswa/submissions': storea3ef875fdb8283f578a11a3e7e42104d,
}

const SubmissionController = { create, store }

export default SubmissionController