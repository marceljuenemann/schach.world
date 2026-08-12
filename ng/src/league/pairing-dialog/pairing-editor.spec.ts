import { PairingEditor } from './pairing-editor';
import { Pairing, Player, Team } from '../types';

function makeTeam(id: number, players: {id: number, dwz: number | null, number: number}[]): Team {
  return {
    id,
    name: `Team ${id}`,
    playersByTeamNumber: {
      1: players.map((p): Player => ({id: p.id, name: `Player ${p.id}`, number: p.number, dwz: p.dwz})),
    },
  }
}

function makePairing(overrides: Partial<Pairing> = {}): Pairing {
  return {
    id: 1,
    team1: {id: 10, name: 'Team A'},
    team2: {id: 20, name: 'Team B'},
    comment: null,
    games: null,
    ...overrides,
  }
}

describe('PairingEditor', () => {
  it('guesses a win for the higher-DWZ player when the difference is >100', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)

    expect(editor.boardRows[0].result1.value).toBe('1')
    expect(editor.boardRows[0].result2.value).toBe('0')
  })

  it('guesses a draw when the DWZ difference is 100 or less', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1800, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1750, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)

    expect(editor.boardRows[0].result1.value).toBe('½')
    expect(editor.boardRows[0].result2.value).toBe('½')
  })

  it('guesses a forfeit when only one side has a player', () => {
    const team1 = makeTeam(10, [])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)

    expect(editor.boardRows[0].player1.value).toBeNull()
    expect(editor.boardRows[0].result1.value).toBe('-')
    expect(editor.boardRows[0].result2.value).toBe('+')
  })

  it('guesses a double forfeit when both sides have no player', () => {
    const editor = new PairingEditor(makePairing(), 1, makeTeam(10, []), makeTeam(20, []))

    expect(editor.boardRows[0].result1.value).toBe('-')
    expect(editor.boardRows[0].result2.value).toBe('-')
  })

  it('mirrors a manual result onto the other side until it is also manual', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1800, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1800, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)
    const row = editor.boardRows[0]

    row.result1.setValue('1')
    editor.onResultSelected(row, row.result1)
    expect(row.result2.value).toBe('0')
    expect(row.result2.isManual).toBeFalse()

    // Once the other side is also set manually, neither gets touched again.
    row.result2.setValue('½')
    editor.onResultSelected(row, row.result2)
    expect(row.result1.value).toBe('1')
    expect(row.result2.value).toBe('½')
  })

  it('re-anchors later, non-manual boards after a manual player pick', () => {
    const team1 = makeTeam(10, [
      {id: 1, dwz: 1800, number: 1},
      {id: 2, dwz: 1800, number: 2},
      {id: 3, dwz: 1800, number: 3},
      {id: 4, dwz: 1800, number: 4},
    ])
    const team2 = makeTeam(20, [{id: 5, dwz: 1800, number: 1}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    // Default: board 1 -> roster[0], board 2 -> roster[1].
    expect(editor.boardRows[0].player1.value).toBe(1)
    expect(editor.boardRows[1].player1.value).toBe(2)

    // Manually swap in the 3rd-ranked player for board 1.
    const row1 = editor.boardRows[0]
    row1.player1.setValue(3)
    editor.onPlayerSelected(row1, row1.player1)

    expect(editor.boardRows[0].player1.value).toBe(3)  // manual, unchanged
    expect(editor.boardRows[1].player1.value).toBe(4)  // continues from just after roster index of player 3
  })

  it('guesses null for later boards after a manual "no player" pick', () => {
    const team1 = makeTeam(10, [
      {id: 1, dwz: 1800, number: 1},
      {id: 2, dwz: 1800, number: 2},
      {id: 3, dwz: 1800, number: 3},
    ])
    const team2 = makeTeam(20, [{id: 4, dwz: 1800, number: 1}])
    const editor = new PairingEditor(makePairing(), 3, team1, team2)

    // Default: board 1 -> roster[0], board 2 -> roster[1], board 3 -> roster[2].
    expect(editor.boardRows[1].player1.value).toBe(2)

    const row1 = editor.boardRows[0]
    row1.player1.setValue(null)
    editor.onPlayerSelected(row1, row1.player1)

    expect(editor.boardRows[0].player1.value).toBeNull()  // manual, unchanged
    expect(editor.boardRows[1].player1.value).toBeNull()  // cascades to null, not roster[1]
    expect(editor.boardRows[2].player1.value).toBeNull()
  })

  it('does not change the other side when a player is manually picked', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1800, number: 1}, {id: 2, dwz: 1800, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1800, number: 1}, {id: 4, dwz: 1800, number: 2}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    const row1 = editor.boardRows[0]
    row1.player1.setValue(2)
    editor.onPlayerSelected(row1, row1.player1)

    expect(editor.boardRows[0].player2.value).toBe(3)
    expect(editor.boardRows[1].player2.value).toBe(4)
  })

  it('cascades a kampflos pattern from boards 1 & 2 onto later boards only', () => {
    const players = (offset: number) => [
      {id: offset + 1, dwz: 1800, number: 1},
      {id: offset + 2, dwz: 1800, number: 2},
      {id: offset + 3, dwz: 1800, number: 3},
    ]
    const team1 = makeTeam(10, players(0))
    const team2 = makeTeam(20, players(10))
    const editor = new PairingEditor(makePairing(), 3, team1, team2)

    // Equal DWZ everywhere - board 3 would otherwise guess a draw.
    expect(editor.boardRows[2].result1.value).toBe('½')

    const [row1, row2] = editor.boardRows
    row1.result1.setValue('+')
    editor.onResultSelected(row1, row1.result1)
    row2.result1.setValue('+')
    editor.onResultSelected(row2, row2.result1)

    expect(editor.boardRows[2].result1.value).toBe('+')
    expect(editor.boardRows[2].result2.value).toBe('-')
  })

  it('does not cascade from any board pair other than 1 & 2', () => {
    const players = (offset: number) => [
      {id: offset + 1, dwz: 1800, number: 1},
      {id: offset + 2, dwz: 1800, number: 2},
      {id: offset + 3, dwz: 1800, number: 3},
      {id: offset + 4, dwz: 1800, number: 4},
    ]
    const team1 = makeTeam(10, players(0))
    const team2 = makeTeam(20, players(10))
    const editor = new PairingEditor(makePairing(), 4, team1, team2)

    const [, row2, row3] = editor.boardRows
    row2.result1.setValue('+')
    editor.onResultSelected(row2, row2.result1)
    row3.result1.setValue('+')
    editor.onResultSelected(row3, row3.result1)

    // Boards 2 & 3 matching should NOT cascade onto board 4.
    expect(editor.boardRows[3].result1.value).toBe('½')
  })

  it('marks pre-existing saved game data as manual and does not overwrite it', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const pairing = makePairing({
      games: [{board: 1, player1: {id: 1, name: 'Player 1', number: 1, dwz: 1900}, player2: null, result1: '0', result2: '1'}],
    })
    const editor = new PairingEditor(pairing, 1, team1, team2)
    const row = editor.boardRows[0]

    // DWZ would guess a win for player 1 (1900 vs blank/0), but the saved '0'/'1' must stand.
    expect(row.result1.value).toBe('0')
    expect(row.result2.value).toBe('1')
    expect(row.result1.isManual).toBeTrue()
    expect(row.player1.isManual).toBeTrue()
  })

  it('computes the overall result from board scores until manually overridden', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1900, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}, {id: 4, dwz: 1700, number: 2}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    // Both boards guess a win for team1 -> overall 2:0.
    expect(editor.overallResult1.value).toBe(2)
    expect(editor.overallResult2.value).toBe(0)

    editor.overallResult1.setValue(1.5)
    editor.onOverallResultChanged(editor.overallResult1)

    // A further board change must not clobber the manual overall override.
    const row = editor.boardRows[0]
    row.result1.setValue('½')
    editor.onResultSelected(row, row.result1)
    expect(editor.overallResult1.value).toBe(1.5)
  })

  it('derives the other overall result from the board count when one side is set manually', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1900, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}, {id: 4, dwz: 1700, number: 2}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    editor.overallResult1.setValue(1.5)
    editor.onOverallResultChanged(editor.overallResult1)

    // 2 boards total -> the other side fills in the remainder, not the board tally (0).
    expect(editor.overallResult2.value).toBe(0.5)
    expect(editor.overallResult2.isManual).toBeFalse()

    // Manually setting the other side too freezes both - neither is derived anymore.
    editor.overallResult2.setValue(1)
    editor.onOverallResultChanged(editor.overallResult2)
    expect(editor.overallResult1.value).toBe(1.5)
    expect(editor.overallResult2.value).toBe(1)
  })
})
