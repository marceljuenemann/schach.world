// Mirrors src/League/Api/Model/*.php - keep in sync when adding fields. Intentionally
// incomplete: only fields actually used on the client are listed here.

export interface Team {
  id: number
  name: string
  playersByTeamNumber?: Record<number, Player[]>
}

export interface Player {
  id: number
  name: string
  number: number
  dwz: number | null
}

export interface Game {
  board: number
  player1: Player | null
  player2: Player | null
  result1: string
  result2: string
}

export interface Pairing {
  id: number
  team1: Team
  team2: Team
  comment: string | null
  games: Game[] | null
}

export interface MatchDay {
  pairings: Pairing[]
}
