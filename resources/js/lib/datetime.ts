import type { ShopTimezone } from '@/types/app-data'

// Every formatter names the shop timezone, so neither the browser's nor the SSR
// server's local timezone can change which day or hour is shown. Strings are assembled
// from parts because separators (such as the space before "PM") differ between ICU
// builds, and the server render must match hydration exactly.

type DatePartType = 'weekday' | 'day' | 'month' | 'year' | 'hour' | 'minute' | 'dayPeriod'
type DateParts = Record<DatePartType, string>

export interface ShopDateFormatOptions {
    weekday?: boolean
}

const formatters = new Map<ShopTimezone, Intl.DateTimeFormat>()

const formatterFor = (timeZone: ShopTimezone): Intl.DateTimeFormat => {
    let formatter = formatters.get(timeZone)

    if (formatter === undefined) {
        formatter = new Intl.DateTimeFormat('en-US', {
            timeZone,
            weekday: 'long',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        })
        formatters.set(timeZone, formatter)
    }

    return formatter
}

const toInstant = (value: string | Date): Date => {
    const instant = value instanceof Date ? value : new Date(value)

    if (Number.isNaN(instant.getTime())) {
        throw new RangeError(`Invalid date value: ${String(value)}`)
    }

    return instant
}

const shopDateParts = (value: string | Date, timeZone: ShopTimezone): DateParts => {
    const parts: DateParts = { weekday: '', day: '', month: '', year: '', hour: '', minute: '', dayPeriod: '' }

    for (const part of formatterFor(timeZone).formatToParts(toInstant(value))) {
        if (part.type in parts) {
            parts[part.type as DatePartType] = part.value
        }
    }

    return parts
}

/** "17 Oct 2026", or "Saturday, 17 Oct 2026" with `weekday`. */
export const formatShopDate = (value: string | Date, timeZone: ShopTimezone, options: ShopDateFormatOptions = {}): string => {
    const parts = shopDateParts(value, timeZone)
    const date = `${parts.day} ${parts.month} ${parts.year}`

    return options.weekday === true ? `${parts.weekday}, ${date}` : date
}

/** "17 Oct 2026, 11:42 PM" in the shop timezone. */
export const formatShopDateTime = (value: string | Date, timeZone: ShopTimezone): string => {
    const parts = shopDateParts(value, timeZone)

    return `${parts.day} ${parts.month} ${parts.year}, ${parts.hour}:${parts.minute} ${parts.dayPeriod.toUpperCase()}`
}
