import { InjectionToken } from "@angular/core"
import { PlayerData } from "./player-data/player-data.component"

export interface Config {
  tournamentName: string
  groups: Array<GroupConfig>
}

export interface GroupConfig {
  id: string
  name: string
  // TODO: add a description for DWZ and such?
}

// TODO: Maybe move this into some dialog helper instead?
export const CONFIG_TOKEN = new InjectionToken<Config>('RegistrationConfig')

export interface Player {
  playerData: PlayerData
}
