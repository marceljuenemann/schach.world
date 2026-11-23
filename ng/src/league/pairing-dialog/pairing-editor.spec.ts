import { GuessableControl, isLateRegistration, lateRegistrationName, PairingEditor } from './pairing-editor';
import { Pairing, Player, Team } from '../types';

function makeTeam(id: number, players: {id: number, dwz: number | null, number: number}[], teamNumber = 1): Team {
  return {
    id,
    name: `Team ${id}`,
    zps: null,
    playersByTeamNumber: {
      [teamNumber]: players.map((p): Player => ({id: p.id, name: `Player ${p.id}`, number: p.number, dwz: p.dwz})),
    },
  }
}

function makePairing(overrides: Partial<Pairing> = {}): Pairing {
  return {
    id: 1,
    team1: {id: 10, name: 'Team A', zps: null},
    team2: {id: 20, name: 'Team B', zps: null},
    comment: null,
    result1: null,
    result2: null,
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
    // result2 is the usual opposite of result1, so it's left guessable - editing result1
    // later should be enough to flip both sides without having to touch result2 too.
    expect(row.result2.isManual).toBeFalse()
  })

  it('keeps a saved empty player slot instead of filling it in from the roster', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1800, number: 2}])
    const team2 = makeTeam(20, [{id: 11, dwz: 1900, number: 1}, {id: 12, dwz: 1800, number: 2}])
    const pairing = makePairing({
      result1: 2,
      result2: 0,
      games: [
        {board: 1, player1: {id: 1, name: 'Player 1', number: 1, dwz: 1900}, player2: {id: 11, name: 'Player 11', number: 1, dwz: 1900}, result1: '1', result2: '0'},
        {board: 2, player1: {id: 2, name: 'Player 2', number: 2, dwz: 1800}, player2: null, result1: '+', result2: '-'},
      ],
    })
    const editor = new PairingEditor(pairing, 2, team1, team2)

    expect(editor.boardRows[1].player2.value).toBeNull()
    expect(editor.toRequest('', false).games[1].player2).toBeNull()
  })

  it('keeps all empty player slots of a saved walkover', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1800, number: 2}])
    const team2 = makeTeam(20, [{id: 11, dwz: 1900, number: 1}, {id: 12, dwz: 1800, number: 2}])
    const pairing = makePairing({
      result1: 2,
      result2: 0,
      games: [1, 2].map(board => ({board, player1: null, player2: null, result1: '+', result2: '-'})),
    })
    const editor = new PairingEditor(pairing, 2, team1, team2)

    for (const row of editor.boardRows) {
      expect(row.player1.value).toBeNull()
      expect(row.player2.value).toBeNull()
      expect(row.result1.value).toBe('+')
      expect(row.result2.value).toBe('-')
    }
  })

  it('still lets a saved empty player slot be filled in by hand', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 11, dwz: 1900, number: 1}])
    const pairing = makePairing({
      games: [{board: 1, player1: {id: 1, name: 'Player 1', number: 1, dwz: 1900}, player2: null, result1: '+', result2: '-'}],
    })
    const editor = new PairingEditor(pairing, 1, team1, team2)
    const row = editor.boardRows[0]

    row.player2.setValue(11)
    editor.onPlayerSelected(row, row.player2)

    expect(editor.toRequest('', false).games[0].player2).toBe(11)
  })

  it('marks a saved result2 as manual too when it is not the usual opposite of result1', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const pairing = makePairing({
      // Not a normal win/loss pair - a special result that must be preserved as-is.
      games: [{board: 1, player1: {id: 1, name: 'Player 1', number: 1, dwz: 1900}, player2: null, result1: '+', result2: '+'}],
    })
    const editor = new PairingEditor(pairing, 1, team1, team2)
    const row = editor.boardRows[0]

    expect(row.result1.value).toBe('+')
    expect(row.result2.value).toBe('+')
    expect(row.result1.isManual).toBeTrue()
    expect(row.result2.isManual).toBeTrue()
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

  it('keeps deriving the other overall result from the board count even as board results change, ignoring the tally', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1900, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}, {id: 4, dwz: 1700, number: 2}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    // A lopsided manual override (e.g. an 8:0-style walkover) that ignores the boards entirely.
    editor.overallResult1.setValue(2)
    editor.onOverallResultChanged(editor.overallResult1)
    expect(editor.overallResult2.value).toBe(0)

    // Board changes must not pull the non-manual side back to the (now irrelevant) tally.
    const row = editor.boardRows[0]
    row.result1.setValue('0')
    editor.onResultSelected(row, row.result1)
    expect(editor.overallResult1.value).toBe(2)
    expect(editor.overallResult2.value).toBe(0)
  })

  it('leaves a saved overall result guessable when it matches the computed board tally, so later board edits keep updating it', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1900, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}, {id: 4, dwz: 1700, number: 2}])
    // Both boards guess a win for team1 -> computed tally is 2:0, matching the saved result.
    const pairing = makePairing({result1: 2, result2: 0})
    const editor = new PairingEditor(pairing, 2, team1, team2)

    expect(editor.overallResult1.value).toBe(2)
    expect(editor.overallResult1.isManual).toBeFalse()
    expect(editor.overallResult2.value).toBe(0)
    expect(editor.overallResult2.isManual).toBeFalse()

    // Since it wasn't frozen as manual, changing a board result updates it live.
    const row = editor.boardRows[0]
    row.result1.setValue('½')
    editor.onResultSelected(row, row.result1)
    expect(editor.overallResult1.value).toBe(1.5)
    expect(editor.overallResult2.value).toBe(0.5)
  })

  it('also freezes the other side as manual when it would not survive the board-count derivation', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}, {id: 2, dwz: 1900, number: 2}])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}, {id: 4, dwz: 1700, number: 2}])
    // Computed tally is 2:0. result1 was adjusted (e.g. a walkover penalty) so it's
    // manual. result2 (0) matches its own tally, but since 1.5 implies 0.5 (board count -
    // result1), 0 wouldn't survive that derivation - so it must be frozen as manual too,
    // preserving the saved 1.5:0 instead of silently becoming 1.5:0.5 on open.
    const pairing = makePairing({result1: 1.5, result2: 0})
    const editor = new PairingEditor(pairing, 2, team1, team2)

    expect(editor.overallResult1.value).toBe(1.5)
    expect(editor.overallResult1.isManual).toBeTrue()
    expect(editor.overallResult2.value).toBe(0)
    expect(editor.overallResult2.isManual).toBeTrue()
  })

  it('leaves the other side guessable when it already matches the board-count derivation', () => {
    // Only 1 player per side for 2 boards: board 2 is a double-blank forfeit, which
    // contributes 0 to both tallies rather than 1 - so the tally sum (1) falls short of
    // the board count (2), letting "matches own tally" and "matches board-count minus the
    // other side" actually differ (with fully resolved boards they'd always agree).
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    // Tally is 1:0. result1=2 doesn't match that, so it's manual. result2=0 does match
    // its own tally (0), and ALSO matches board count - result1 (2 - 2 = 0), so it can
    // safely stay guessable/derived from result1.
    const pairing = makePairing({result1: 2, result2: 0})
    const editor = new PairingEditor(pairing, 2, team1, team2)

    expect(editor.overallResult1.value).toBe(2)
    expect(editor.overallResult1.isManual).toBeTrue()
    expect(editor.overallResult2.value).toBe(0)
    expect(editor.overallResult2.isManual).toBeFalse()
  })

  it('groups the roster by team number, including the team\'s own group', () => {
    const team1: Team = {
      id: 10, name: 'Team A', zps: null,
      playersByTeamNumber: {
        1: [{id: 1, name: 'Player 1', number: 1, dwz: 1900}],
        2: [{id: 2, name: 'Player 2', number: 1, dwz: 1800}],
      },
    }
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)

    expect(editor.rosterGroups1.map(g => g.teamNumber)).toEqual([1, 2])
    expect(editor.rosterGroups1[0].players.map(p => p.id)).toEqual([1])
    expect(editor.rosterGroups1[1].players.map(p => p.id)).toEqual([2])
  })

  it('lets a board be set to a late registration and cascades later boards to null', () => {
    const team1 = makeTeam(10, [
      {id: 1, dwz: 1900, number: 1},
      {id: 2, dwz: 1900, number: 2},
    ])
    const team2 = makeTeam(20, [{id: 3, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)

    const row1 = editor.boardRows[0]
    editor.addLateRegistration(row1, row1.player1, {teamNumber: 1, player: {lastName: 'Guest'}})

    expect(isLateRegistration(row1.player1.value)).toBeTrue()
    // Later boards on that side guess null, not the next roster player (id 2).
    expect(editor.boardRows[1].player1.value).toBeNull()
  })

  it('uses the late registered player\'s own DWZ for guessing, defaulting to 0 when unset', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 1, team1, team2)
    const row = editor.boardRows[0]

    // Late registration with a high DWZ should win against team2's 1700.
    row.player1.setValue({teamNumber: 1, player: {lastName: 'Guest', dwz: 2000}})
    editor.onPlayerSelected(row, row.player1)
    expect(row.result1.value).toBe('1')
    expect(row.result2.value).toBe('0')

    // Late registration with no DWZ defaults to 0, so team2 (1700) wins.
    row.player1.setValue({teamNumber: 1, player: {lastName: 'Guest'}})
    editor.onPlayerSelected(row, row.player1)
    expect(row.result1.value).toBe('0')
    expect(row.result2.value).toBe('1')
  })

  it('offers a late registration on all boards of its side only', () => {
    const team1 = makeTeam(10, [{id: 1, dwz: 1900, number: 1}])
    const team2 = makeTeam(20, [{id: 2, dwz: 1700, number: 1}])
    const editor = new PairingEditor(makePairing(), 2, team1, team2)
    const lateRegistration = {teamNumber: 1, player: {lastName: 'Guest'}}

    editor.addLateRegistration(editor.boardRows[0], editor.boardRows[0].player2, lateRegistration)

    expect(editor.boardRows[0].player2.value).toBe(lateRegistration)
    expect(editor.lateRegistrations2).toEqual([lateRegistration])
    expect(editor.lateRegistrations1).toEqual([])
  })

  it('lists a late registration in the group of its team', () => {
    const team1: Team = {id: 10, name: 'Team 10', zps: null, playersByTeamNumber: {
      1: [{id: 1, name: 'Player 1', number: 1, dwz: 1900}],
      2: [{id: 2, name: 'Player 2', number: 2, dwz: 1800}],
    }}
    const editor = new PairingEditor(makePairing(), 1, team1, makeTeam(20, []))
    const row = editor.boardRows[0]
    const lateRegistration = {teamNumber: 1, player: {lastName: 'Guest'}}

    editor.addLateRegistration(row, row.player1, lateRegistration)

    expect(editor.rosterGroups1[0].lateRegistrations).toEqual([lateRegistration])
    expect(editor.rosterGroups1[1].lateRegistrations).toEqual([])
  })

  it('adds a group for a late registration in a team without a roster', () => {
    const editor = new PairingEditor(makePairing(), 1, makeTeam(10, [], 2), makeTeam(20, []))
    const row = editor.boardRows[0]

    editor.addLateRegistration(row, row.player1, {teamNumber: 1, player: {lastName: 'Guest'}})

    expect(editor.rosterGroups1.map(g => g.teamNumber)).toEqual([1, 2])
    expect(editor.rosterGroups1[0].players).toEqual([])
  })

  it('formats the name of a late registration', () => {
    expect(lateRegistrationName({teamNumber: 1, player: {lastName: 'Neumann', firstName: 'Nora'}})).toBe('Neumann, Nora')
    expect(lateRegistrationName({teamNumber: 1, player: {lastName: 'Neumann'}})).toBe('Neumann')
  })

  it('remembers the previous value of a control', () => {
    const control = new GuessableControl<number | null>(1)
    control.setValue(2)
    expect(control.previousValue).toBe(1)
  })
})
