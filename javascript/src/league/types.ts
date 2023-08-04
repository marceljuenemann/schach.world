export interface Team {
  id: number,
  name: string,
  zps?: string
  uri: string
  venue?: any,
  captain?: any
}

export interface Pairing {
  id: number,
  round: number,
  team1: Team,
  team2: Team,
  host?: Team,
  result?: string,
  result1?: number,
  result2?: number,
  wasMoved: boolean,
  moveDate?: string
}
