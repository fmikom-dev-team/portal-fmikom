export const KUESIONER_CATEGORIES = ['Alumni', 'Stakeholder'] as const;
export type KuesionerCategory = (typeof KUESIONER_CATEGORIES)[number];

export const YEARS = Array.from({ length: 10 }, (_, i) =>
    (new Date().getFullYear() + 5 - i).toString(),
);

export const PROGRAMS_STUDY = ['Informatika', 'Sistem Informasi', 'Matematika'];
