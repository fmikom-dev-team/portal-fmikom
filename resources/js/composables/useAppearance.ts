import type { ComputedRef, Ref } from 'vue';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

const STUDENT_COMPONENT_PREFIX = 'Wims/Mahasiswa/';
const DEFAULT_APPEARANCE_KEY = 'appearance';

export function isStudentThemeRoute(componentName?: string | null): boolean {
    return (componentName ?? '').startsWith(STUDENT_COMPONENT_PREFIX);
}

export function resolveAppearanceStorageKey(userId?: number | string | null): string {
    return userId ? `${DEFAULT_APPEARANCE_KEY}_student_${userId}` : DEFAULT_APPEARANCE_KEY;
}

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia(
            '(prefers-color-scheme: dark)',
        );
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle(
            'dark',
            systemTheme === 'dark',
        );
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = (userId?: number | string | null) => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem(resolveAppearanceStorageKey(userId)) as Appearance | null;
};

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const handleSystemThemeChange = (userId?: number | string | null) => {
    const currentAppearance = getStoredAppearance(userId);

    updateTheme(currentAppearance || 'system');
};

export function forceLightTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
}

export function syncThemeForComponent(componentName?: string | null, userId?: number | string | null): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!isStudentThemeRoute(componentName)) {
        forceLightTheme();
        return;
    }

    const savedAppearance = getStoredAppearance(userId);
    updateTheme(savedAppearance || 'system');
}

export function initializeTheme(componentName?: string | null, userId?: number | string | null): void {
    if (typeof window === 'undefined') {
        return;
    }

    syncThemeForComponent(componentName, userId);
    mediaQuery()?.addEventListener('change', () => handleSystemThemeChange(userId));
}

const appearance = ref<Appearance>('system');

export function useAppearance(): UseAppearanceReturn {
    const page = usePage<{
        component?: string | null;
        auth?: {
            user?: {
                id?: number | null;
            } | null;
        };
    }>();
    const currentComponent = computed(() => page.component ?? null);
    const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

    const syncAppearanceState = (userId?: number | string | null) => {
        const savedAppearance = getStoredAppearance(userId);

        if (savedAppearance) {
            appearance.value = savedAppearance;
        } else {
            appearance.value = 'system';
        }

        syncThemeForComponent(currentComponent.value, userId);
    };

    watch([currentUserId, currentComponent], ([userId]) => {
        syncAppearanceState(userId);
    }, {
        immediate: true,
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        const storageKey = resolveAppearanceStorageKey(currentUserId.value);

        localStorage.setItem(storageKey, value);

        setCookie(storageKey, value);

        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
