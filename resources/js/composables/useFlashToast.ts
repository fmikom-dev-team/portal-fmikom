import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

/**
 * Initialize global flash toast handler using Inertia's router events.
 * Call once in app.ts — no need to call in layouts or pages.
 *
 * Uses router.on('finish') to reliably fire ONCE per navigation,
 * preventing duplicate toasts from watch reactivity.
 */
export function initFlashToast() {
    router.on('finish', () => {
        const flash = (router.page?.props as any)?.flash;
        if (!flash) return;
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.warning) toast.warning(flash.warning);
        if (flash.info) toast.info(flash.info);
    });
}
