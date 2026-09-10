import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/documents/surat/{id}/generate'
 */
const generate4dc0087b29b62374302632a274ce7cf8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'get',
})

generate4dc0087b29b62374302632a274ce7cf8.definition = {
    methods: ["get","head"],
    url: '/documents/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/documents/surat/{id}/generate'
 */
generate4dc0087b29b62374302632a274ce7cf8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generate4dc0087b29b62374302632a274ce7cf8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/documents/surat/{id}/generate'
 */
generate4dc0087b29b62374302632a274ce7cf8.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/documents/surat/{id}/generate'
 */
generate4dc0087b29b62374302632a274ce7cf8.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate4dc0087b29b62374302632a274ce7cf8.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/admin/surat/{id}/generate'
 */
const generate03a498acc6b7534dc5a6f2f7299d0045 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'get',
})

generate03a498acc6b7534dc5a6f2f7299d0045.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/generate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/admin/surat/{id}/generate'
 */
generate03a498acc6b7534dc5a6f2f7299d0045.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generate03a498acc6b7534dc5a6f2f7299d0045.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/admin/surat/{id}/generate'
 */
generate03a498acc6b7534dc5a6f2f7299d0045.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::generate
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:245
 * @route '/admin/surat/{id}/generate'
 */
generate03a498acc6b7534dc5a6f2f7299d0045.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generate03a498acc6b7534dc5a6f2f7299d0045.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::generate, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `generate['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const generate = {
    '/documents/surat/{id}/generate': generate4dc0087b29b62374302632a274ce7cf8,
    '/admin/surat/{id}/generate': generate03a498acc6b7534dc5a6f2f7299d0045,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/admin/surat/create'
 */
const create1426ee4ccb383d4ff8a8c5c14ffb6fb5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create1426ee4ccb383d4ff8a8c5c14ffb6fb5.url(options),
    method: 'get',
})

create1426ee4ccb383d4ff8a8c5c14ffb6fb5.definition = {
    methods: ["get","head"],
    url: '/admin/surat/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/admin/surat/create'
 */
create1426ee4ccb383d4ff8a8c5c14ffb6fb5.url = (options?: RouteQueryOptions) => {
    return create1426ee4ccb383d4ff8a8c5c14ffb6fb5.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/admin/surat/create'
 */
create1426ee4ccb383d4ff8a8c5c14ffb6fb5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create1426ee4ccb383d4ff8a8c5c14ffb6fb5.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/admin/surat/create'
 */
create1426ee4ccb383d4ff8a8c5c14ffb6fb5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create1426ee4ccb383d4ff8a8c5c14ffb6fb5.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/kaprodi/admin/surat/create'
 */
const created5f64d2b7060ece2769fbec7bee33745 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: created5f64d2b7060ece2769fbec7bee33745.url(options),
    method: 'get',
})

created5f64d2b7060ece2769fbec7bee33745.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/kaprodi/admin/surat/create'
 */
created5f64d2b7060ece2769fbec7bee33745.url = (options?: RouteQueryOptions) => {
    return created5f64d2b7060ece2769fbec7bee33745.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/kaprodi/admin/surat/create'
 */
created5f64d2b7060ece2769fbec7bee33745.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: created5f64d2b7060ece2769fbec7bee33745.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/kaprodi/admin/surat/create'
 */
created5f64d2b7060ece2769fbec7bee33745.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: created5f64d2b7060ece2769fbec7bee33745.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/dekan/admin/surat/create'
 */
const createf1e25c0457bd48e673c5f72f8ac1e66d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createf1e25c0457bd48e673c5f72f8ac1e66d.url(options),
    method: 'get',
})

createf1e25c0457bd48e673c5f72f8ac1e66d.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/dekan/admin/surat/create'
 */
createf1e25c0457bd48e673c5f72f8ac1e66d.url = (options?: RouteQueryOptions) => {
    return createf1e25c0457bd48e673c5f72f8ac1e66d.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/dekan/admin/surat/create'
 */
createf1e25c0457bd48e673c5f72f8ac1e66d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createf1e25c0457bd48e673c5f72f8ac1e66d.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::create
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:35
 * @route '/dekan/admin/surat/create'
 */
createf1e25c0457bd48e673c5f72f8ac1e66d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createf1e25c0457bd48e673c5f72f8ac1e66d.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::create, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `create['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const create = {
    '/admin/surat/create': create1426ee4ccb383d4ff8a8c5c14ffb6fb5,
    '/kaprodi/admin/surat/create': created5f64d2b7060ece2769fbec7bee33745,
    '/dekan/admin/surat/create': createf1e25c0457bd48e673c5f72f8ac1e66d,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/admin/surat/select-type'
 */
const selectTypee4f35a3e04d1cd196550ba29ddccef8f = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectTypee4f35a3e04d1cd196550ba29ddccef8f.url(options),
    method: 'post',
})

selectTypee4f35a3e04d1cd196550ba29ddccef8f.definition = {
    methods: ["post"],
    url: '/admin/surat/select-type',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/admin/surat/select-type'
 */
selectTypee4f35a3e04d1cd196550ba29ddccef8f.url = (options?: RouteQueryOptions) => {
    return selectTypee4f35a3e04d1cd196550ba29ddccef8f.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/admin/surat/select-type'
 */
selectTypee4f35a3e04d1cd196550ba29ddccef8f.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectTypee4f35a3e04d1cd196550ba29ddccef8f.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/kaprodi/admin/surat/select-type'
 */
const selectType321985e3a63edca923836f6db80e5b5d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectType321985e3a63edca923836f6db80e5b5d.url(options),
    method: 'post',
})

selectType321985e3a63edca923836f6db80e5b5d.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/select-type',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/kaprodi/admin/surat/select-type'
 */
selectType321985e3a63edca923836f6db80e5b5d.url = (options?: RouteQueryOptions) => {
    return selectType321985e3a63edca923836f6db80e5b5d.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/kaprodi/admin/surat/select-type'
 */
selectType321985e3a63edca923836f6db80e5b5d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectType321985e3a63edca923836f6db80e5b5d.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/dekan/admin/surat/select-type'
 */
const selectTypef4bcebf31a3868b44e141d46218e93e1 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectTypef4bcebf31a3868b44e141d46218e93e1.url(options),
    method: 'post',
})

selectTypef4bcebf31a3868b44e141d46218e93e1.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/select-type',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/dekan/admin/surat/select-type'
 */
selectTypef4bcebf31a3868b44e141d46218e93e1.url = (options?: RouteQueryOptions) => {
    return selectTypef4bcebf31a3868b44e141d46218e93e1.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::selectType
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:71
 * @route '/dekan/admin/surat/select-type'
 */
selectTypef4bcebf31a3868b44e141d46218e93e1.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: selectTypef4bcebf31a3868b44e141d46218e93e1.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::selectType, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `selectType['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const selectType = {
    '/admin/surat/select-type': selectTypee4f35a3e04d1cd196550ba29ddccef8f,
    '/kaprodi/admin/surat/select-type': selectType321985e3a63edca923836f6db80e5b5d,
    '/dekan/admin/surat/select-type': selectTypef4bcebf31a3868b44e141d46218e93e1,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/admin/surat/form/{jenisSurat}'
 */
const form8a79520a8d6806df604bfe52baf7f53c = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form8a79520a8d6806df604bfe52baf7f53c.url(args, options),
    method: 'get',
})

form8a79520a8d6806df604bfe52baf7f53c.definition = {
    methods: ["get","head"],
    url: '/admin/surat/form/{jenisSurat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/admin/surat/form/{jenisSurat}'
 */
form8a79520a8d6806df604bfe52baf7f53c.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return form8a79520a8d6806df604bfe52baf7f53c.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/admin/surat/form/{jenisSurat}'
 */
form8a79520a8d6806df604bfe52baf7f53c.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form8a79520a8d6806df604bfe52baf7f53c.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/admin/surat/form/{jenisSurat}'
 */
form8a79520a8d6806df604bfe52baf7f53c.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form8a79520a8d6806df604bfe52baf7f53c.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/kaprodi/admin/surat/form/{jenisSurat}'
 */
const formc2b51663df625bc828b8904c869e68d8 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: formc2b51663df625bc828b8904c869e68d8.url(args, options),
    method: 'get',
})

formc2b51663df625bc828b8904c869e68d8.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/form/{jenisSurat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/kaprodi/admin/surat/form/{jenisSurat}'
 */
formc2b51663df625bc828b8904c869e68d8.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return formc2b51663df625bc828b8904c869e68d8.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/kaprodi/admin/surat/form/{jenisSurat}'
 */
formc2b51663df625bc828b8904c869e68d8.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: formc2b51663df625bc828b8904c869e68d8.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/kaprodi/admin/surat/form/{jenisSurat}'
 */
formc2b51663df625bc828b8904c869e68d8.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: formc2b51663df625bc828b8904c869e68d8.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/dekan/admin/surat/form/{jenisSurat}'
 */
const form4f3b4e5881682e7e9b354ec2e13e3e37 = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form4f3b4e5881682e7e9b354ec2e13e3e37.url(args, options),
    method: 'get',
})

form4f3b4e5881682e7e9b354ec2e13e3e37.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/form/{jenisSurat}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/dekan/admin/surat/form/{jenisSurat}'
 */
form4f3b4e5881682e7e9b354ec2e13e3e37.url = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return form4f3b4e5881682e7e9b354ec2e13e3e37.definition.url
            .replace('{jenisSurat}', parsedArgs.jenisSurat.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/dekan/admin/surat/form/{jenisSurat}'
 */
form4f3b4e5881682e7e9b354ec2e13e3e37.get = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form4f3b4e5881682e7e9b354ec2e13e3e37.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::form
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:86
 * @route '/dekan/admin/surat/form/{jenisSurat}'
 */
form4f3b4e5881682e7e9b354ec2e13e3e37.head = (args: { jenisSurat: number | { id: number } } | [jenisSurat: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form4f3b4e5881682e7e9b354ec2e13e3e37.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::form, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `form['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const form = {
    '/admin/surat/form/{jenisSurat}': form8a79520a8d6806df604bfe52baf7f53c,
    '/kaprodi/admin/surat/form/{jenisSurat}': formc2b51663df625bc828b8904c869e68d8,
    '/dekan/admin/surat/form/{jenisSurat}': form4f3b4e5881682e7e9b354ec2e13e3e37,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/admin/surat/subjects/search'
 */
const searchSubjects5a89c56f5141a7a384a5ba62957d4181 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects5a89c56f5141a7a384a5ba62957d4181.url(options),
    method: 'get',
})

searchSubjects5a89c56f5141a7a384a5ba62957d4181.definition = {
    methods: ["get","head"],
    url: '/admin/surat/subjects/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/admin/surat/subjects/search'
 */
searchSubjects5a89c56f5141a7a384a5ba62957d4181.url = (options?: RouteQueryOptions) => {
    return searchSubjects5a89c56f5141a7a384a5ba62957d4181.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/admin/surat/subjects/search'
 */
searchSubjects5a89c56f5141a7a384a5ba62957d4181.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects5a89c56f5141a7a384a5ba62957d4181.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/admin/surat/subjects/search'
 */
searchSubjects5a89c56f5141a7a384a5ba62957d4181.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchSubjects5a89c56f5141a7a384a5ba62957d4181.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/kaprodi/admin/surat/subjects/search'
 */
const searchSubjects5f083eac81bef1da2162e0a3288e6e17 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects5f083eac81bef1da2162e0a3288e6e17.url(options),
    method: 'get',
})

searchSubjects5f083eac81bef1da2162e0a3288e6e17.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/subjects/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/kaprodi/admin/surat/subjects/search'
 */
searchSubjects5f083eac81bef1da2162e0a3288e6e17.url = (options?: RouteQueryOptions) => {
    return searchSubjects5f083eac81bef1da2162e0a3288e6e17.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/kaprodi/admin/surat/subjects/search'
 */
searchSubjects5f083eac81bef1da2162e0a3288e6e17.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects5f083eac81bef1da2162e0a3288e6e17.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/kaprodi/admin/surat/subjects/search'
 */
searchSubjects5f083eac81bef1da2162e0a3288e6e17.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchSubjects5f083eac81bef1da2162e0a3288e6e17.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/dekan/admin/surat/subjects/search'
 */
const searchSubjects937d78400f11c2c1af671c7917fc2221 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects937d78400f11c2c1af671c7917fc2221.url(options),
    method: 'get',
})

searchSubjects937d78400f11c2c1af671c7917fc2221.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/subjects/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/dekan/admin/surat/subjects/search'
 */
searchSubjects937d78400f11c2c1af671c7917fc2221.url = (options?: RouteQueryOptions) => {
    return searchSubjects937d78400f11c2c1af671c7917fc2221.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/dekan/admin/surat/subjects/search'
 */
searchSubjects937d78400f11c2c1af671c7917fc2221.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchSubjects937d78400f11c2c1af671c7917fc2221.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:153
 * @route '/dekan/admin/surat/subjects/search'
 */
searchSubjects937d78400f11c2c1af671c7917fc2221.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchSubjects937d78400f11c2c1af671c7917fc2221.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::searchSubjects, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `searchSubjects['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const searchSubjects = {
    '/admin/surat/subjects/search': searchSubjects5a89c56f5141a7a384a5ba62957d4181,
    '/kaprodi/admin/surat/subjects/search': searchSubjects5f083eac81bef1da2162e0a3288e6e17,
    '/dekan/admin/surat/subjects/search': searchSubjects937d78400f11c2c1af671c7917fc2221,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/admin/surat/preview/html'
 */
const previewHtml56f27c4dfef678d3647a97732bce18bc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtml56f27c4dfef678d3647a97732bce18bc.url(options),
    method: 'get',
})

previewHtml56f27c4dfef678d3647a97732bce18bc.definition = {
    methods: ["get","head"],
    url: '/admin/surat/preview/html',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/admin/surat/preview/html'
 */
previewHtml56f27c4dfef678d3647a97732bce18bc.url = (options?: RouteQueryOptions) => {
    return previewHtml56f27c4dfef678d3647a97732bce18bc.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/admin/surat/preview/html'
 */
previewHtml56f27c4dfef678d3647a97732bce18bc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtml56f27c4dfef678d3647a97732bce18bc.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/admin/surat/preview/html'
 */
previewHtml56f27c4dfef678d3647a97732bce18bc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewHtml56f27c4dfef678d3647a97732bce18bc.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/kaprodi/admin/surat/preview/html'
 */
const previewHtml537e5af17e919f016fb2ca8edc063c55 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtml537e5af17e919f016fb2ca8edc063c55.url(options),
    method: 'get',
})

previewHtml537e5af17e919f016fb2ca8edc063c55.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/preview/html',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/kaprodi/admin/surat/preview/html'
 */
previewHtml537e5af17e919f016fb2ca8edc063c55.url = (options?: RouteQueryOptions) => {
    return previewHtml537e5af17e919f016fb2ca8edc063c55.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/kaprodi/admin/surat/preview/html'
 */
previewHtml537e5af17e919f016fb2ca8edc063c55.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtml537e5af17e919f016fb2ca8edc063c55.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/kaprodi/admin/surat/preview/html'
 */
previewHtml537e5af17e919f016fb2ca8edc063c55.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewHtml537e5af17e919f016fb2ca8edc063c55.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/dekan/admin/surat/preview/html'
 */
const previewHtmla8392c760879e8b8986091432e7d7984 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtmla8392c760879e8b8986091432e7d7984.url(options),
    method: 'get',
})

previewHtmla8392c760879e8b8986091432e7d7984.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/preview/html',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/dekan/admin/surat/preview/html'
 */
previewHtmla8392c760879e8b8986091432e7d7984.url = (options?: RouteQueryOptions) => {
    return previewHtmla8392c760879e8b8986091432e7d7984.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/dekan/admin/surat/preview/html'
 */
previewHtmla8392c760879e8b8986091432e7d7984.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewHtmla8392c760879e8b8986091432e7d7984.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:209
 * @route '/dekan/admin/surat/preview/html'
 */
previewHtmla8392c760879e8b8986091432e7d7984.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewHtmla8392c760879e8b8986091432e7d7984.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::previewHtml, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewHtml['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewHtml = {
    '/admin/surat/preview/html': previewHtml56f27c4dfef678d3647a97732bce18bc,
    '/kaprodi/admin/surat/preview/html': previewHtml537e5af17e919f016fb2ca8edc063c55,
    '/dekan/admin/surat/preview/html': previewHtmla8392c760879e8b8986091432e7d7984,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/admin/surat/preview'
 */
const previewPage8707cb51fadf86af9239dcc2df8a0d00 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPage8707cb51fadf86af9239dcc2df8a0d00.url(options),
    method: 'get',
})

previewPage8707cb51fadf86af9239dcc2df8a0d00.definition = {
    methods: ["get","head"],
    url: '/admin/surat/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/admin/surat/preview'
 */
previewPage8707cb51fadf86af9239dcc2df8a0d00.url = (options?: RouteQueryOptions) => {
    return previewPage8707cb51fadf86af9239dcc2df8a0d00.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/admin/surat/preview'
 */
previewPage8707cb51fadf86af9239dcc2df8a0d00.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPage8707cb51fadf86af9239dcc2df8a0d00.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/admin/surat/preview'
 */
previewPage8707cb51fadf86af9239dcc2df8a0d00.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewPage8707cb51fadf86af9239dcc2df8a0d00.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/kaprodi/admin/surat/preview'
 */
const previewPaged4ff3f79f4e9a9556fd119e6f7edb755 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPaged4ff3f79f4e9a9556fd119e6f7edb755.url(options),
    method: 'get',
})

previewPaged4ff3f79f4e9a9556fd119e6f7edb755.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/kaprodi/admin/surat/preview'
 */
previewPaged4ff3f79f4e9a9556fd119e6f7edb755.url = (options?: RouteQueryOptions) => {
    return previewPaged4ff3f79f4e9a9556fd119e6f7edb755.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/kaprodi/admin/surat/preview'
 */
previewPaged4ff3f79f4e9a9556fd119e6f7edb755.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPaged4ff3f79f4e9a9556fd119e6f7edb755.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/kaprodi/admin/surat/preview'
 */
previewPaged4ff3f79f4e9a9556fd119e6f7edb755.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewPaged4ff3f79f4e9a9556fd119e6f7edb755.url(options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/dekan/admin/surat/preview'
 */
const previewPagee98480f2ca2f9b83424bc5963070a2d4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPagee98480f2ca2f9b83424bc5963070a2d4.url(options),
    method: 'get',
})

previewPagee98480f2ca2f9b83424bc5963070a2d4.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/dekan/admin/surat/preview'
 */
previewPagee98480f2ca2f9b83424bc5963070a2d4.url = (options?: RouteQueryOptions) => {
    return previewPagee98480f2ca2f9b83424bc5963070a2d4.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/dekan/admin/surat/preview'
 */
previewPagee98480f2ca2f9b83424bc5963070a2d4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewPagee98480f2ca2f9b83424bc5963070a2d4.url(options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::previewPage
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:193
 * @route '/dekan/admin/surat/preview'
 */
previewPagee98480f2ca2f9b83424bc5963070a2d4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewPagee98480f2ca2f9b83424bc5963070a2d4.url(options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::previewPage, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `previewPage['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const previewPage = {
    '/admin/surat/preview': previewPage8707cb51fadf86af9239dcc2df8a0d00,
    '/kaprodi/admin/surat/preview': previewPaged4ff3f79f4e9a9556fd119e6f7edb755,
    '/dekan/admin/surat/preview': previewPagee98480f2ca2f9b83424bc5963070a2d4,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/admin/surat/preview'
 */
const preview8707cb51fadf86af9239dcc2df8a0d00 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview8707cb51fadf86af9239dcc2df8a0d00.url(options),
    method: 'post',
})

preview8707cb51fadf86af9239dcc2df8a0d00.definition = {
    methods: ["post"],
    url: '/admin/surat/preview',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/admin/surat/preview'
 */
preview8707cb51fadf86af9239dcc2df8a0d00.url = (options?: RouteQueryOptions) => {
    return preview8707cb51fadf86af9239dcc2df8a0d00.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/admin/surat/preview'
 */
preview8707cb51fadf86af9239dcc2df8a0d00.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview8707cb51fadf86af9239dcc2df8a0d00.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/kaprodi/admin/surat/preview'
 */
const previewd4ff3f79f4e9a9556fd119e6f7edb755 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: previewd4ff3f79f4e9a9556fd119e6f7edb755.url(options),
    method: 'post',
})

previewd4ff3f79f4e9a9556fd119e6f7edb755.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/preview',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/kaprodi/admin/surat/preview'
 */
previewd4ff3f79f4e9a9556fd119e6f7edb755.url = (options?: RouteQueryOptions) => {
    return previewd4ff3f79f4e9a9556fd119e6f7edb755.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/kaprodi/admin/surat/preview'
 */
previewd4ff3f79f4e9a9556fd119e6f7edb755.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: previewd4ff3f79f4e9a9556fd119e6f7edb755.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/dekan/admin/surat/preview'
 */
const previewe98480f2ca2f9b83424bc5963070a2d4 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: previewe98480f2ca2f9b83424bc5963070a2d4.url(options),
    method: 'post',
})

previewe98480f2ca2f9b83424bc5963070a2d4.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/preview',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/dekan/admin/surat/preview'
 */
previewe98480f2ca2f9b83424bc5963070a2d4.url = (options?: RouteQueryOptions) => {
    return previewe98480f2ca2f9b83424bc5963070a2d4.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::preview
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:179
 * @route '/dekan/admin/surat/preview'
 */
previewe98480f2ca2f9b83424bc5963070a2d4.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: previewe98480f2ca2f9b83424bc5963070a2d4.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::preview, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `preview['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const preview = {
    '/admin/surat/preview': preview8707cb51fadf86af9239dcc2df8a0d00,
    '/kaprodi/admin/surat/preview': previewd4ff3f79f4e9a9556fd119e6f7edb755,
    '/dekan/admin/surat/preview': previewe98480f2ca2f9b83424bc5963070a2d4,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/admin/surat/store'
 */
const storeba4f8621df23c4a11d93b52f7fdc9b17 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeba4f8621df23c4a11d93b52f7fdc9b17.url(options),
    method: 'post',
})

storeba4f8621df23c4a11d93b52f7fdc9b17.definition = {
    methods: ["post"],
    url: '/admin/surat/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/admin/surat/store'
 */
storeba4f8621df23c4a11d93b52f7fdc9b17.url = (options?: RouteQueryOptions) => {
    return storeba4f8621df23c4a11d93b52f7fdc9b17.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/admin/surat/store'
 */
storeba4f8621df23c4a11d93b52f7fdc9b17.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeba4f8621df23c4a11d93b52f7fdc9b17.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/kaprodi/admin/surat/store'
 */
const storefcaf9c90326584d9150a59d639a726b8 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storefcaf9c90326584d9150a59d639a726b8.url(options),
    method: 'post',
})

storefcaf9c90326584d9150a59d639a726b8.definition = {
    methods: ["post"],
    url: '/kaprodi/admin/surat/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/kaprodi/admin/surat/store'
 */
storefcaf9c90326584d9150a59d639a726b8.url = (options?: RouteQueryOptions) => {
    return storefcaf9c90326584d9150a59d639a726b8.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/kaprodi/admin/surat/store'
 */
storefcaf9c90326584d9150a59d639a726b8.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storefcaf9c90326584d9150a59d639a726b8.url(options),
    method: 'post',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/dekan/admin/surat/store'
 */
const stored9e0eeca32b83c966747d6650e6a3375 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stored9e0eeca32b83c966747d6650e6a3375.url(options),
    method: 'post',
})

stored9e0eeca32b83c966747d6650e6a3375.definition = {
    methods: ["post"],
    url: '/dekan/admin/surat/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/dekan/admin/surat/store'
 */
stored9e0eeca32b83c966747d6650e6a3375.url = (options?: RouteQueryOptions) => {
    return stored9e0eeca32b83c966747d6650e6a3375.definition.url + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::store
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:222
 * @route '/dekan/admin/surat/store'
 */
stored9e0eeca32b83c966747d6650e6a3375.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stored9e0eeca32b83c966747d6650e6a3375.url(options),
    method: 'post',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/admin/surat/store': storeba4f8621df23c4a11d93b52f7fdc9b17,
    '/kaprodi/admin/surat/store': storefcaf9c90326584d9150a59d639a726b8,
    '/dekan/admin/surat/store': stored9e0eeca32b83c966747d6650e6a3375,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/admin/surat/{id}/edit'
 */
const edit75856c3816cc3442df74e71499cfe47f = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit75856c3816cc3442df74e71499cfe47f.url(args, options),
    method: 'get',
})

edit75856c3816cc3442df74e71499cfe47f.definition = {
    methods: ["get","head"],
    url: '/admin/surat/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/admin/surat/{id}/edit'
 */
edit75856c3816cc3442df74e71499cfe47f.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit75856c3816cc3442df74e71499cfe47f.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/admin/surat/{id}/edit'
 */
edit75856c3816cc3442df74e71499cfe47f.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit75856c3816cc3442df74e71499cfe47f.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/admin/surat/{id}/edit'
 */
edit75856c3816cc3442df74e71499cfe47f.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit75856c3816cc3442df74e71499cfe47f.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/kaprodi/admin/surat/{id}/edit'
 */
const edit8c888f2614757efb48e090fa9b8ce32e = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit8c888f2614757efb48e090fa9b8ce32e.url(args, options),
    method: 'get',
})

edit8c888f2614757efb48e090fa9b8ce32e.definition = {
    methods: ["get","head"],
    url: '/kaprodi/admin/surat/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/kaprodi/admin/surat/{id}/edit'
 */
edit8c888f2614757efb48e090fa9b8ce32e.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit8c888f2614757efb48e090fa9b8ce32e.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/kaprodi/admin/surat/{id}/edit'
 */
edit8c888f2614757efb48e090fa9b8ce32e.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit8c888f2614757efb48e090fa9b8ce32e.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/kaprodi/admin/surat/{id}/edit'
 */
edit8c888f2614757efb48e090fa9b8ce32e.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit8c888f2614757efb48e090fa9b8ce32e.url(args, options),
    method: 'head',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/dekan/admin/surat/{id}/edit'
 */
const editcb8817cddfc774bcfd57bbf0190c9f18 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editcb8817cddfc774bcfd57bbf0190c9f18.url(args, options),
    method: 'get',
})

editcb8817cddfc774bcfd57bbf0190c9f18.definition = {
    methods: ["get","head"],
    url: '/dekan/admin/surat/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/dekan/admin/surat/{id}/edit'
 */
editcb8817cddfc774bcfd57bbf0190c9f18.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return editcb8817cddfc774bcfd57bbf0190c9f18.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/dekan/admin/surat/{id}/edit'
 */
editcb8817cddfc774bcfd57bbf0190c9f18.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editcb8817cddfc774bcfd57bbf0190c9f18.url(args, options),
    method: 'get',
})
/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::edit
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:263
 * @route '/dekan/admin/surat/{id}/edit'
 */
editcb8817cddfc774bcfd57bbf0190c9f18.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editcb8817cddfc774bcfd57bbf0190c9f18.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::edit, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `edit['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const edit = {
    '/admin/surat/{id}/edit': edit75856c3816cc3442df74e71499cfe47f,
    '/kaprodi/admin/surat/{id}/edit': edit8c888f2614757efb48e090fa9b8ce32e,
    '/dekan/admin/surat/{id}/edit': editcb8817cddfc774bcfd57bbf0190c9f18,
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/admin/surat/{id}'
 */
const updatecaa2593158145898f04ae0567d4630f4 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatecaa2593158145898f04ae0567d4630f4.url(args, options),
    method: 'patch',
})

updatecaa2593158145898f04ae0567d4630f4.definition = {
    methods: ["patch"],
    url: '/admin/surat/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/admin/surat/{id}'
 */
updatecaa2593158145898f04ae0567d4630f4.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return updatecaa2593158145898f04ae0567d4630f4.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/admin/surat/{id}'
 */
updatecaa2593158145898f04ae0567d4630f4.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatecaa2593158145898f04ae0567d4630f4.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/kaprodi/admin/surat/{id}'
 */
const update927fcee68fb401f2468c1f9e0dccf577 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update927fcee68fb401f2468c1f9e0dccf577.url(args, options),
    method: 'patch',
})

update927fcee68fb401f2468c1f9e0dccf577.definition = {
    methods: ["patch"],
    url: '/kaprodi/admin/surat/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/kaprodi/admin/surat/{id}'
 */
update927fcee68fb401f2468c1f9e0dccf577.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update927fcee68fb401f2468c1f9e0dccf577.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/kaprodi/admin/surat/{id}'
 */
update927fcee68fb401f2468c1f9e0dccf577.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update927fcee68fb401f2468c1f9e0dccf577.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/dekan/admin/surat/{id}'
 */
const update02a2db8a6087a1edeb8a0ca8e2e42db2 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
    method: 'patch',
})

update02a2db8a6087a1edeb8a0ca8e2e42db2.definition = {
    methods: ["patch"],
    url: '/dekan/admin/surat/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/dekan/admin/surat/{id}'
 */
update02a2db8a6087a1edeb8a0ca8e2e42db2.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update02a2db8a6087a1edeb8a0ca8e2e42db2.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Modules\Fast\Controllers\Admin\LetterController::update
 * @see app/Modules/Fast/Controllers/Admin/LetterController.php:315
 * @route '/dekan/admin/surat/{id}'
 */
update02a2db8a6087a1edeb8a0ca8e2e42db2.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update02a2db8a6087a1edeb8a0ca8e2e42db2.url(args, options),
    method: 'patch',
})

/**
* Multiple routes resolve to \App\Modules\Fast\Controllers\Admin\LetterController::update, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `update['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const update = {
    '/admin/surat/{id}': updatecaa2593158145898f04ae0567d4630f4,
    '/kaprodi/admin/surat/{id}': update927fcee68fb401f2468c1f9e0dccf577,
    '/dekan/admin/surat/{id}': update02a2db8a6087a1edeb8a0ca8e2e42db2,
}

const LetterController = { generate, create, selectType, form, searchSubjects, previewHtml, previewPage, preview, store, edit, update }

export default LetterController