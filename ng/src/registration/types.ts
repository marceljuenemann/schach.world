import { InjectionToken } from "@angular/core"
import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  tournamentName: string
  groups: Map<string, GroupConfig>
}

export interface GroupConfig {
  id: string
  name: string
  // TODO: add a description for DWZ and such?
  maxDwz?: number
  minYearOfBirth?: number
}

export interface ContactDetails {
  name: string,
  email: string
}

export interface Player {
  playerData: PlayerData,
  contactDetails: ContactDetails
}
