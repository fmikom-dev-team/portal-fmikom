import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
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
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.url = (options?: RouteQueryOptions) => {
    return create92801d7540bdfe071e00b64abeede393.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
const create92801d7540bdfe071e00b64abeede393Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create92801d7540bdfe071e00b64abeede393.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/fast/user/ajukan'
*/
create92801d7540bdfe071e00b64abeede393Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create92801d7540bdfe071e00b64abeede393.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create92801d7540bdfe071e00b64abeede393.form = create92801d7540bdfe071e00b64abeede393Form
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
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
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.url = (options?: RouteQueryOptions) => {
    return create02d423413b9e77609d83f7fc6e747101.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'head',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
const create02d423413b9e77609d83f7fc6e747101Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create02d423413b9e77609d83f7fc6e747101.url(options),
    method: 'get',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::create
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:29
* @route '/mahasiswa/ajukan'
*/
create02d423413b9e77609d83f7fc6e747101Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create02d423413b9e77609d83f7fc6e747101.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create02d423413b9e77609d83f7fc6e747101.form = create02d423413b9e77609d83f7fc6e747101Form

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
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
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
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
storefa8f1b7ec6e51ab02156722b8385a8d5.url = (options?: RouteQueryOptions) => {
    return storefa8f1b7ec6e51ab02156722b8385a8d5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
storefa8f1b7ec6e51ab02156722b8385a8d5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storefa8f1b7ec6e51ab02156722b8385a8d5.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
const storefa8f1b7ec6e51ab02156722b8385a8d5Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storefa8f1b7ec6e51ab02156722b8385a8d5.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/fast/user/submissions'
*/
storefa8f1b7ec6e51ab02156722b8385a8d5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storefa8f1b7ec6e51ab02156722b8385a8d5.url(options),
    method: 'post',
})

storefa8f1b7ec6e51ab02156722b8385a8d5.form = storefa8f1b7ec6e51ab02156722b8385a8d5Form
/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
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
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/mahasiswa/submissions'
*/
storea3ef875fdb8283f578a11a3e7e42104d.url = (options?: RouteQueryOptions) => {
    return storea3ef875fdb8283f578a11a3e7e42104d.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/mahasiswa/submissions'
*/
storea3ef875fdb8283f578a11a3e7e42104d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storea3ef875fdb8283f578a11a3e7e42104d.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/mahasiswa/submissions'
*/
const storea3ef875fdb8283f578a11a3e7e42104dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storea3ef875fdb8283f578a11a3e7e42104d.url(options),
    method: 'post',
})

/**
* @see \App\Modules\Fast\Controllers\Mahasiswa\SubmissionController::store
* @see app/Modules/Fast/Controllers/Mahasiswa/SubmissionController.php:121
* @route '/mahasiswa/submissions'
*/
storea3ef875fdb8283f578a11a3e7e42104dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storea3ef875fdb8283f578a11a3e7e42104d.url(options),
    method: 'post',
})

storea3ef875fdb8283f578a11a3e7e42104d.form = storea3ef875fdb8283f578a11a3e7e42104dForm

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