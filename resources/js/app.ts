import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import { initializeTheme, syncThemeForComponent } from '@/composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({
            setup() {
                initializeTheme(
                    props.initialPage.component,
                    props.initialPage.props.auth?.user?.id ?? null,
                );

                router.on('navigate', (event) => {
                    const page = event.detail.page;

                    syncThemeForComponent(
                        page.component,
                        page.props?.auth?.user?.id ?? null,
                    );
                });

                return () => h(App, props);
            },
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
