import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export type OptionalWithoutUndefined<T> = {
    [K in keyof T as undefined extends T[K] ? never : K]: T[K]
} & {
    [K in keyof T as undefined extends T[K] ? K : never]?: Exclude<T[K], undefined>
}

export function omitUndefinedProps<T extends object>(input: T): OptionalWithoutUndefined<T> {
    return Object.fromEntries(Object.entries(input).filter(([, value]) => value !== undefined)) as OptionalWithoutUndefined<T>
}

export function getYears() {
    const startYear = 1900
    const currentYear = new Date().getFullYear()
    return Array.from({ length: currentYear - startYear + 1 }, (_, i) => currentYear - i)
}

export function getMonths() {
    return Array.from({ length: 12 }, (_, i) => new Date(0, i).toLocaleString('default', { month: 'long' }))
}

export const isObjectRecord = (value: unknown): value is Record<string, unknown> => typeof value === 'object' && value !== null

export function capitalize(value: string): string {
    if (value === '') return ''
    return value.charAt(0).toUpperCase() + value.slice(1)
}

export function formatDate(value: string, locale?: string): string {
    return new Date(value).toLocaleDateString(locale)
}

export function getInitials(fullName?: string): string {
    if (!fullName) return ''

    const names = fullName.trim().split(' ')
    const firstName = names[0] ?? ''

    if (names.length === 0) return ''
    if (names.length === 1) return firstName.charAt(0).toUpperCase()

    const lastName = names[names.length - 1] ?? ''

    return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

type StringEnum = Record<string, string>

export function getEnumOptions<TEnum extends StringEnum>(enumType: TEnum): Array<{ value: TEnum[keyof TEnum]; label: string }> {
    return (Object.values(enumType) as Array<TEnum[keyof TEnum]>).map((value) => ({
        value,
        label: value
    }))
}
