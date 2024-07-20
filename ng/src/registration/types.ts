export interface Config {
  tournamentName: string
  groups: Array<GroupConfig>
}

export interface GroupConfig {
  id: string
  name: string
  // TODO: add a description for DWZ and such?
}
