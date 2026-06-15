<?php

use Illuminate\Support\Facades\File;

it('maps every WIMS inertia render path to an existing vue page', function () {
    $root = base_path();
    $renderLocations = [
        app_path('Modules/Wims/Controllers'),
        app_path('Modules/Wims/Services'),
    ];

    $renders = collect($renderLocations)
        ->filter(fn (string $path) => File::exists($path))
        ->flatMap(function (string $path) use ($root) {
            return collect(File::allFiles($path))
                ->map(function (\SplFileInfo $file) use ($root) {
                    $contents = File::get($file->getPathname());
                    preg_match_all("/Inertia::render\\(\\s*['\"]([^'\"]+)['\"]/", $contents, $matches);

                    return collect($matches[1] ?? [])->map(fn (string $renderPath) => [
                        'source' => str_replace('\\', '/', str_replace($root.'/', '', $file->getPathname())),
                        'render_path' => $renderPath,
                        'vue_path' => resource_path('js/pages/'.str_replace('/', DIRECTORY_SEPARATOR, $renderPath).'.vue'),
                    ]);
                })
                ->flatten(1);
        })
        ->values();

    expect($renders)->not->toBeEmpty();

    foreach ($renders as $render) {
        expect($render['render_path'])->toStartWith('Modules/Wims/');
        expect(
            File::exists($render['vue_path']),
            sprintf(
                'Missing Vue page for %s rendered from %s',
                $render['render_path'],
                $render['source'],
            ),
        )->toBeTrue();
    }
});
