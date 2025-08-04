import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  id: string
  tournamentName: string
  deadline: string
  maxPlayers?: number | null
  groups: GroupConfig[]
  links: Record<string, string>
  termsAndConditions: string
}

export interface GroupConfig {
  id: string
  name: string
  minDwz?: number | null
  maxDwz?: number | null
  minYearOfBirth?: number | null
  maxPlayers?: number | null
}

export interface ContactDetails {
  name: string,
  email: string
}

export interface Player {
  id: number,
  group: string,
  playerData: PlayerData,
  contactDetails: ContactDetails
}
