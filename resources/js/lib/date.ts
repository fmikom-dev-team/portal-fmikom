const INDONESIAN_LOCALE = 'id-ID';

const shortMonthMap: Record<string, string> = {
    jan: 'Jan',
    january: 'Jan',
    feb: 'Feb',
    february: 'Feb',
    mar: 'Mar',
    march: 'Mar',
    apr: 'Apr',
    april: 'Apr',
    may: 'Mei',
    jun: 'Jun',
    june: 'Jun',
    jul: 'Jul',
    july: 'Jul',
    aug: 'Agu',
    august: 'Agu',
    sep: 'Sep',
    sept: 'Sep',
    september: 'Sep',
    oct: 'Okt',
    october: 'Okt',
    nov: 'Nov',
    november: 'Nov',
    dec: 'Des',
    december: 'Des',
};

function normalizeShortMonth(month: string): string {
    return shortMonthMap[month.toLowerCase()] ?? month;
}

function parseDateOnly(value: string): Date | null {
    const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) {
        return null;
    }

    const [, year, month, day] = match;

    return new Date(Number(year), Number(month) - 1, Number(day));
}

export function formatIndonesianDateLabel(value?: string | null): string {
    if (!value) {
        return '-';
    }

    const trimmed = value.trim();
    const dateOnly = parseDateOnly(trimmed);

    if (dateOnly) {
        return new Intl.DateTimeFormat(INDONESIAN_LOCALE, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        })
            .format(dateOnly)
            .replace(/\./g, '');
    }

    const textMatch = trimmed.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/);

    if (textMatch) {
        const [, day, month, year] = textMatch;
        return `${day.padStart(2, '0')} ${normalizeShortMonth(month)} ${year}`;
    }

    return trimmed.replace(
        /\b(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Sept|Oct|Nov|Dec)\b/gi,
        (match) => normalizeShortMonth(match),
    );
}

export function formatIndonesianDateTime(
    value?: string | Date | null,
    options: Intl.DateTimeFormatOptions = {},
): string {
    if (!value) {
        return '-';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return new Intl.DateTimeFormat(INDONESIAN_LOCALE, options).format(date).replace(/\./g, '');
}

export function formatIndonesianTime(value?: string | number | null): string {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const normalizedValue = String(value).trim();

    if (!normalizedValue) {
        return '-';
    }

    const trimmed = normalizedValue;
    const digitsOnly = trimmed.replace(/[^\d]/g, '');

    if (/^\d{2}:\d{2}(:\d{2})?$/.test(trimmed)) {
        return trimmed.slice(0, 5);
    }

    if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(trimmed)) {
        const [hours, minutes] = trimmed.split(':');
        return `${hours.padStart(2, '0')}:${minutes}`;
    }

    if (/^\d{3,4}$/.test(digitsOnly)) {
        const padded = digitsOnly.padStart(4, '0');
        return `${padded.slice(0, 2)}:${padded.slice(2, 4)}`;
    }

    if (/^\d{5,6}$/.test(digitsOnly)) {
        const padded = digitsOnly.padStart(6, '0');
        return `${padded.slice(0, 2)}:${padded.slice(2, 4)}`;
    }

    const date = new Date(trimmed);

    if (!Number.isNaN(date.getTime())) {
        return new Intl.DateTimeFormat(INDONESIAN_LOCALE, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(date).replace(/\./g, ':');
    }

    return trimmed;
}
