import { genericCompare } from './util'

describe('genericCompare', () => {
  it('compares numbers ascending', () => {
    expect(genericCompare(1, 2, 'asc')).toBeLessThan(0)
    expect(genericCompare(2, 1, 'asc')).toBeGreaterThan(0)
    expect(genericCompare(5, 5, 'asc')).toBe(0)
  })

  it('compares numbers descending', () => {
    expect(genericCompare(1, 2, 'desc')).toBeGreaterThan(0)
    expect(genericCompare(2, 1, 'desc')).toBeLessThan(0)
  })

  it('compares strings with localeCompare', () => {
    expect(genericCompare('a', 'b', 'asc')).toBeLessThan(0)
    expect(genericCompare('b', 'a', 'asc')).toBeGreaterThan(0)
    expect(genericCompare('a', 'a', 'asc')).toBe(0)
    expect(genericCompare('a', 'b', 'desc')).toBeGreaterThan(0)
  })

  it('puts nulls last by default', () => {
    expect(genericCompare(null, 1, 'asc')).toBeGreaterThan(0)
    expect(genericCompare(1, null, 'asc')).toBeLessThan(0)
  })

  it('puts nulls first when nullsFirst is true', () => {
    expect(genericCompare(null, 1, 'asc', true)).toBeLessThan(0)
    expect(genericCompare(1, null, 'asc', true)).toBeGreaterThan(0)
  })
})
