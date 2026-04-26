import { NsvFormConfig } from "../core/form/form-group"
import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  id: string
  tournamentName: string
  registrationStart?: string | null
  deadline: string
  maxPlayers?: number | null
  groups: GroupConfig[]
  constraints?: RegistrationConstraint[]
  links: Record<string, string>
  termsAndConditions: string
  additionalFields?: NsvFormConfig[]
}

export interface GroupConfig {
  id: string
  name: string
  minDwz?: number | null
  maxDwz?: number | null
  minYearOfBirth?: number | null
  requireFideId?: boolean
  // TODO: Replace with RegistrationConstraint once the config is edited via a UI?
  maxPlayers?: number | null
  hidden?: boolean
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
  additionalFields?: Record<string, string>,
  contactDetails: ContactDetails,
  created?: string
}
