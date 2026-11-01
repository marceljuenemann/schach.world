// Mirrors src/League/Api/Model/*.php - keep in sync when adding fields. Intentionally
// incomplete: only fields actually used on the client are listed here.

export interface Team {
  id: number
  name: string
  zps: string | null
  playersByTeamNumber?: Record<number, Player[]>
}

export interface Player {
  id: number
  name: string
  number: number
  dwz: number | null
}

// Mirrors Nsv\League\Api\Request\CreateOrUpdatePlayerRequest.php - the shape a
// not-yet-existing late registered player is captured as.
export interface PlayerRequest {
  firstName?: string
  lastName: string
  title?: string
  zps?: string
  dwz?: number
  elo?: number
  yearOfBirth?: number
  gender?: '' | 'm' | 'w' | 'd'
}

// A board filled by a not-yet-registered player: which team-number group
// (playersByTeamNumber key - the team's own is one of these too) they're being
// registered onto, plus the player data itself.
export interface LateRegistration {
  teamNumber: number
  player: PlayerRequest
}

// Mirrors Nsv\League\Api\Request\UpdatePairingGameRequest.php
export interface UpdatePairingGameRequest {
  board: number
  player1: number | null
  lateRegistration1: LateRegistration | null
  player2: number | null
  lateRegistration2: LateRegistration | null
  result1: string
  result2: string
}

// Mirrors Nsv\League\Api\Request\UpdatePairingRequest.php
export interface UpdatePairingRequest {
  result1: number
  result2: number
  comment: string
  sendConfirmation: boolean
  games: UpdatePairingGameRequest[]
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
  result1: number | null
  result2: number | null
  games: Game[] | null
}

export interface MatchDay {
  pairings: Pairing[]
}
