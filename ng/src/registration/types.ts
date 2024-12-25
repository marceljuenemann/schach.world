import { InjectionToken } from "@angular/core"
import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  tournamentName: string
  groups: GroupConfig[]
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
  id: number,
  group: string,
  playerData: PlayerData,
  contactDetails: ContactDetails
}
