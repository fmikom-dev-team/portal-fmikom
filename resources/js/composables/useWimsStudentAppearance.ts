import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

const STORAGE_KEY = 'wims-student-appearance';

type UseWimsStudentAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

const appearance = ref<Appearance>('light');

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const getStoredAppearance = (): Appearance | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem(STORAGE_KEY) as Appearance | null;
};

export function useWimsStudentAppearance(): UseWimsStudentAppearanceReturn {
    onMounted(() => {
        const savedAppearance = getStoredAppearance();

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    const updateAppearance = (value: Appearance) => {
        appearance.value = value;

        if (typeof window !== 'undefined') {
            localStorage.setItem(STORAGE_KEY, value);
        }
    };

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
