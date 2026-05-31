import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function isSameUrl(
    url1: NonNullable<InertiaLinkProps['href']>,
    url2: NonNullable<InertiaLinkProps['href']>,
) {
    return resolveUrl(url1) === resolveUrl(url2);
}

export function resolveUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function formatDateWithPattern(value: string | null | undefined, pattern = 'Y-m-d'): string {
    if (!value) return '-';
    const date = new Date(value);
    if (isNaN(date.getTime())) return value;

    const yyyy = date.getFullYear().toString();
    const yy = yyyy.slice(-2);
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const m = String(date.getMonth() + 1);
    const dd = String(date.getDate()).padStart(2, '0');
    const d = String(date.getDate());

    return pattern
        .replace(/Y/g, yyyy)
        .replace(/y/g, yy)
        .replace(/m/g, mm)
        .replace(/n/g, m)
        .replace(/d/g, dd)
        .replace(/j/g, d);
}
