import { FormControl } from '@angular/forms';
import { Pairing, Player, Team } from '../types';

export const RESULT_OPTIONS = ['1', '½', '0', '+', '-', '?'] as const

const SCORE: Record<string, number> = {'1': 1, '½': 0.5, '+': 1}
const OPPOSITE: Record<string, string> = {'1': '0', '0': '1', '+': '-', '-': '+'}  // '½'/'?' mirror themselves

// Angular's typed FormControl can't be subclassed with an explicit generic argument
// (ɵFormControlCtor has multiple construct signatures, so TS rejects `extends
// FormControl<T>` - "Base constructors must all have the same return type"). Extending
// the bare, untyped FormControl and re-typing value/setValue ourselves works around it -
// same trick this codebase's own NsvFormControl already uses.
export class GuessableControl<T> extends FormControl {
  isManual = false
  declare value: T

  constructor(value: T) {
    super(value)
  }

  override setValue(value: T, options?: {onlySelf?: boolean, emitEvent?: boolean}): void {
    super.setValue(value, options)
  }
}

export interface BoardRow {
  board: number
  player1: GuessableControl<number | null>
  player2: GuessableControl<number | null>
  result1: GuessableControl<string>
  result2: GuessableControl<string>
}

export class PairingEditor {
  boardRows: BoardRow[] = []
  overallResult1 = new GuessableControl<number>(0)
  overallResult2 = new GuessableControl<number>(0)

  constructor(
    private pairing: Pairing,
    boardCount: number,
    public readonly team1: Team,
    public readonly team2: Team,
  ) {
    for (let board = 1; board <= boardCount; board++) {
      this.boardRows.push(this.buildRow(board))
    }
    this.guessPlayers(1)
    this.guessPlayers(2)
    for (const row of this.boardRows) this.guessBoard(row)
    this.guessOverallResult()
  }

  private buildRow(board: number): BoardRow {
    const game = this.pairing.games?.find(g => g.board === board) ?? null
    const row: BoardRow = {
      board,
      player1: new GuessableControl<number | null>(game?.player1?.id ?? null),
      player2: new GuessableControl<number | null>(game?.player2?.id ?? null),
      result1: new GuessableControl<string>(game?.result1 ?? '?'),
      result2: new GuessableControl<string>(game?.result2 ?? '?'),
    }
    // Pre-existing saved data counts as already decided - matches legacy's edit-mode
    // behavior (bearbeitung.js marks everything "touched" on load), so we don't
    // silently overwrite a real saved result with a fresh guess.
    if (game && game.result1 !== '?') {
      row.result1.isManual = true
      row.result2.isManual = true
    }
    if (game?.player1) row.player1.isManual = true
    if (game?.player2) row.player2.isManual = true
    return row
  }

  onPlayerSelected(row: BoardRow, control: GuessableControl<number | null>) {
    control.isManual = true
    const side: 1 | 2 = control === row.player1 ? 1 : 2
    this.guessPlayers(side)
    for (const r of this.boardRows) this.guessBoard(r)
    this.guessOverallResult()
  }

  onResultSelected(row: BoardRow, control: GuessableControl<string>) {
    control.isManual = true
    // Re-guess every board, not just this one: a change to board 1 or 2 can affect the
    // kampflos cascade onto board 3+, so a single-row recompute isn't enough here.
    for (const r of this.boardRows) this.guessBoard(r)
    this.guessOverallResult()
  }

  onOverallResultChanged(control: GuessableControl<number>) {
    control.isManual = true
    this.guessOverallResult()
  }

  // Fills in non-manual player slots for one side, in roster order. A manual pick
  // re-anchors the sequence: subsequent non-manual boards continue from just after that
  // player's roster position, rather than restarting from board 1's mapping - this is
  // what makes "select a player, later boards guess too" work whether it's the very
  // first pick or a correction partway through the lineup.
  private guessPlayers(side: 1 | 2) {
    const roster = side === 1 ? this.roster1 : this.roster2
    let rosterIndex = 0
    // A manual "no player" pick means the lineup ends there - later non-manual boards
    // guess null too, rather than skipping the gap and continuing with the next roster player.
    let forceNull = false
    for (const row of this.boardRows) {
      const control = side === 1 ? row.player1 : row.player2
      if (control.isManual) {
        if (control.value === null) {
          forceNull = true
        } else {
          const idx = roster.findIndex(p => p.id === control.value)
          rosterIndex = idx >= 0 ? idx + 1 : rosterIndex
          forceNull = false
        }
        continue
      }
      if (forceNull) {
        control.setValue(null)
        continue
      }
      const next = roster[rosterIndex] ?? null
      control.setValue(next ? next.id : null)
      rosterIndex++
    }
  }

  private guessBoard(row: BoardRow) {
    // Kampflos cascade: boards 1 & 2 showing a consistent forfeit/unknown pattern means
    // the whole match is a walkover - later boards inherit it instead of DWZ-guessing
    // individually. Only boards 1 & 2 ever trigger this (no generalizing to other pairs).
    if (row.board > 2 && !row.result1.isManual && !row.result2.isManual) {
      const cascade = this.kampflosCascade()
      if (cascade) {
        row.result1.setValue(cascade[0])
        row.result2.setValue(cascade[1])
        return
      }
    }

    if (row.result1.isManual || row.result2.isManual) {
      if (row.result1.isManual && !row.result2.isManual) {
        row.result2.setValue(this.opposite(row.result1.value))
      } else if (row.result2.isManual && !row.result1.isManual) {
        row.result1.setValue(this.opposite(row.result2.value))
      }
      return
    }

    const p1 = row.player1.value
    const p2 = row.player2.value
    if (p1 === null && p2 === null) { row.result1.setValue('-'); row.result2.setValue('-'); return }
    if (p1 === null) { row.result1.setValue('-'); row.result2.setValue('+'); return }
    if (p2 === null) { row.result1.setValue('+'); row.result2.setValue('-'); return }

    const dwz1 = this.dwzFor(this.team1, p1) ?? 0
    const dwz2 = this.dwzFor(this.team2, p2) ?? 0
    if (dwz1 > dwz2 + 100) { row.result1.setValue('1'); row.result2.setValue('0') }
    else if (dwz2 > dwz1 + 100) { row.result1.setValue('0'); row.result2.setValue('1') }
    else { row.result1.setValue('½'); row.result2.setValue('½') }
  }

  private kampflosCascade(): [string, string] | null {
    const [b1, b2] = this.boardRows
    if (!b1 || !b2) return null
    const pattern = (r: BoardRow) => `${r.result1.value}:${r.result2.value}`
    if (pattern(b1) === '+:-' && pattern(b2) === '+:-') return ['+', '-']
    if (pattern(b1) === '-:+' && pattern(b2) === '-:+') return ['-', '+']
    if (pattern(b1) === '?:?' && pattern(b2) === '?:?') return ['?', '?']
    if (pattern(b1) === '-:-' && pattern(b2) === '-:-') return ['-', '-']
    return null
  }

  private opposite(result: string): string {
    return OPPOSITE[result] ?? result
  }

  private dwzFor(team: Team, playerId: number): number | null {
    for (const players of Object.values(team.playersByTeamNumber ?? {})) {
      const player = players.find(p => p.id === playerId)
      if (player) return player.dwz
    }
    return null
  }

  get roster1(): Player[] { return this.flattenRoster(this.team1) }
  get roster2(): Player[] { return this.flattenRoster(this.team2) }
  private flattenRoster(team: Team): Player[] {
    return Object.values(team.playersByTeamNumber ?? {}).flat()
  }

  // Options for the overall-result selects: 0, 0.5, 1, ... up to boardCount.
  get overallResultOptions(): number[] {
    return Array.from({length: this.boardRows.length * 2 + 1}, (_, i) => i / 2)
  }

  private guessOverallResult() {
    // A manually-set side takes priority: the other side is derived from it (total points
    // across both sides always equals the board count), not from the board tally - matches
    // how a manual per-board result mirrors onto its other side.
    if (this.overallResult1.isManual && !this.overallResult2.isManual) {
      this.overallResult2.setValue(this.boardRows.length - this.overallResult1.value)
    } else if (this.overallResult2.isManual && !this.overallResult1.isManual) {
      this.overallResult1.setValue(this.boardRows.length - this.overallResult2.value)
    } else {
      if (!this.overallResult1.isManual) this.overallResult1.setValue(this.computedTotal(1))
      if (!this.overallResult2.isManual) this.overallResult2.setValue(this.computedTotal(2))
    }
  }

  private computedTotal(side: 1 | 2): number {
    return this.boardRows.reduce((sum, row) => {
      const result = (side === 1 ? row.result1 : row.result2).value
      return sum + (SCORE[result] ?? 0)
    }, 0)
  }
}
