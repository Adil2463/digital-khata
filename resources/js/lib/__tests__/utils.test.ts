import { describe, expect, expectTypeOf, it } from 'vitest'
import { capitalize, formatDate, getInitials, isObjectRecord, omitUndefinedProps } from '../utils'

it('omits undefined props while preserving required keys, false, and null', () => {
    const input: { value: string; name: string | undefined; disabled: boolean; modelValue: string | null } = {
        value: 'required',
        name: undefined,
        disabled: false,
        modelValue: null
    }
    const forwarded = omitUndefinedProps(input)
    expectTypeOf(forwarded).toExtend<{ value: string; name?: string; disabled: boolean; modelValue: string | null }>()
    expect(forwarded).toEqual({ value: 'required', disabled: false, modelValue: null })
})

describe('isObjectRecord', () => {
    it('returns true for plain objects', () => {
        expect(isObjectRecord({})).toBe(true)
        expect(isObjectRecord({ key: 'value' })).toBe(true)
    })

    it('returns false for null', () => {
        expect(isObjectRecord(null)).toBe(false)
    })

    it('returns false for primitives', () => {
        expect(isObjectRecord(42)).toBe(false)
        expect(isObjectRecord('string')).toBe(false)
        expect(isObjectRecord(true)).toBe(false)
        expect(isObjectRecord(undefined)).toBe(false)
    })

    it('returns true for arrays (object type)', () => {
        expect(isObjectRecord([1, 2, 3])).toBe(true)
    })
})

describe('capitalize', () => {
    it('capitalizes a lowercase string', () => {
        expect(capitalize('admin')).toBe('Admin')
    })

    it('returns empty string for empty input', () => {
        expect(capitalize('')).toBe('')
    })

    it('handles single character', () => {
        expect(capitalize('a')).toBe('A')
    })

    it('preserves already-capitalized string', () => {
        expect(capitalize('Admin')).toBe('Admin')
    })

    it('only capitalizes the first character', () => {
        expect(capitalize('hello world')).toBe('Hello world')
    })
})

describe('formatDate', () => {
    it('formats a valid date string', () => {
        const result = formatDate('2024-01-15')
        expect(typeof result).toBe('string')
        expect(result.length).toBeGreaterThan(0)
    })

    it('formats a datetime string', () => {
        const result = formatDate('2024-06-01T12:00:00Z')
        expect(typeof result).toBe('string')
        expect(result.length).toBeGreaterThan(0)
    })
})

describe('getInitials', () => {
    it('returns initials from a full name', () => {
        expect(getInitials('John Doe')).toBe('JD')
    })

    it('returns single initial for a single name', () => {
        expect(getInitials('John')).toBe('J')
    })

    it('returns empty string for empty input', () => {
        expect(getInitials('')).toBe('')
    })

    it('returns empty string for undefined', () => {
        expect(getInitials(undefined)).toBe('')
    })

    it('handles three-part names by using first and last', () => {
        expect(getInitials('John Michael Doe')).toBe('JD')
    })

    it('uppercases initials', () => {
        expect(getInitials('john doe')).toBe('JD')
    })
})
