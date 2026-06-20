import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function resolveFastPageName(name: string): string {
    if (name.startsWith('approval/')) return `Modules/Fast/Shared/${name}`;
    if (name.startsWith('kaprodi/approval/')) return `Modules/Fast/Kaprodi/${name.slice('kaprodi/'.length)}`;
    if (name.startsWith('dekan/approval/')) return `Modules/Fast/Dekan/${name.slice('dekan/'.length)}`;
    if (name.startsWith('mahasiswa/')) return `Modules/Fast/Mahasiswa/${name.slice('mahasiswa/'.length)}`;
    if (name.startsWith('dosen/')) return `Modules/Fast/Dosen/${name.slice('dosen/'.length)}`;
    if (name.startsWith('admin/')) return `Modules/Fast/Admin/${name.slice('admin/'.length)}`;
    if (name.startsWith('fast/user/')) {
        return `Modules/Fast/Mahasiswa/${name.slice('fast/user/'.length)}`;
    }
    if (name.startsWith('fast/mahasiswa/')) {
        return `Modules/Fast/Mahasiswa/${name.slice('fast/mahasiswa/'.length)}`;
    }

    return name;
}

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${resolveFastPageName(name)}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) }).use(plugin),
        }),
    { cluster: true },
);
