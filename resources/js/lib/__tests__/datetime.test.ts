import { ShopTimezone } from '@/types/app-data'
import { afterEach, describe, expect, it } from 'vitest'
import { formatShopDate, formatShopDateTime } from '../datetime'

const karachi = ShopTimezone.AsiaKarachi
const originalTimezone = process.env.TZ

afterEach(() => {
    if (originalTimezone === undefined) {
        delete process.env.TZ
    } else {
        process.env.TZ = originalTimezone
    }
})

describe('shop date formatting', () => {
    it('keeps the last UTC second before 19:00 on the previous Pakistan day', () => {
        expect(formatShopDateTime('2026-10-16T18:59:59+00:00', karachi)).toBe('16 Oct 2026, 11:59 PM')
        expect(formatShopDate('2026-10-16T18:59:59+00:00', karachi, { weekday: true })).toBe('Friday, 16 Oct 2026')
    })

    it('starts the new Pakistan day at 19:00 UTC', () => {
        expect(formatShopDateTime('2026-10-16T19:00:00+00:00', karachi)).toBe('17 Oct 2026, 12:00 AM')
        expect(formatShopDate('2026-10-16T19:00:00+00:00', karachi, { weekday: true })).toBe('Saturday, 17 Oct 2026')
    })

    it('treats an entry at 00:30 PKT as the new day', () => {
        expect(formatShopDate('2026-10-16T19:30:00Z', karachi)).toBe('17 Oct 2026')
        expect(formatShopDateTime('2026-10-17T00:30:00+05:00', karachi)).toBe('17 Oct 2026, 12:30 AM')
    })

    it('crosses the year boundary in shop time, not UTC', () => {
        expect(formatShopDateTime('2026-12-31T19:00:00Z', karachi)).toBe('1 Jan 2027, 12:00 AM')
    })

    it('formats noon and accepts Date instances', () => {
        expect(formatShopDateTime(new Date('2026-10-17T07:05:00Z'), karachi)).toBe('17 Oct 2026, 12:05 PM')
    })

    it('ignores the timezone of the computer running the code', () => {
        process.env.TZ = 'America/New_York'

        expect(formatShopDateTime('2026-10-16T19:00:00Z', karachi)).toBe('17 Oct 2026, 12:00 AM')
    })

    it('rejects values that are not dates', () => {
        expect(() => formatShopDate('not-a-date', karachi)).toThrow(RangeError)
    })
})
