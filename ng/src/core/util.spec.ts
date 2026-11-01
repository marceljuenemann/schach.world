import { downloadJson, genericCompare } from './util'

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

describe('downloadJson', () => {
  let blob: Blob
  let link: HTMLAnchorElement

  beforeEach(() => {
    spyOn(URL, 'createObjectURL').and.callFake((b: Blob | MediaSource) => {
      blob = b as Blob
      return 'blob:test'
    })
    spyOn(HTMLAnchorElement.prototype, 'click').and.callFake(function (this: HTMLAnchorElement) {
      link = this
    })
  })

  it('escapes non-ASCII characters', async () => {
    const data = { name: 'Müller', city: 'Köln', emoji: '😀' }
    downloadJson(data, 'test.json')

    const content = await blob.text()
    expect(content).toContain('"M\\u00fcller"')
    expect(content).toContain('"K\\u00f6ln"')
    expect(content).toContain('"\\ud83d\\ude00"')
    expect(content).toMatch(/^[\x00-\x7F]*$/)
    expect(JSON.parse(content)).toEqual(data)
  })

  it('triggers a download with the given filename', () => {
    downloadJson({}, 'test.json')
    expect(link.getAttribute('download')).toBe('test.json')
    expect(blob.type).toContain('application/json')
  })
})
