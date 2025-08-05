import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  id: string
  tournamentName: string
  deadline: string
  maxPlayers?: number | null
  groups: GroupConfig[]
  constraints?: RegistrationConstraint[]
  links: Record<string, string>
  termsAndConditions: string
}

export interface GroupConfig {
  id: string
  name: string
  minDwz?: number | null
  maxDwz?: number | null
  minYearOfBirth?: number | null
  // TODO: Replace with RegistrationConstraint once the config is edited via a UI?
  maxPlayers?: number | null
}

/* Registration constraint that applies across multiple groups */
export interface RegistrationConstraint {
  groups: string[]
  maxPlayers?: number | null
}

export interface ContactDetails {
  name: string,
  email: string
}

export interface Player {
  id: number,
  group: string,
  waitlist?: boolean,
  playerData: PlayerData,
  contactDetails: ContactDetails,
  created?: string
}
