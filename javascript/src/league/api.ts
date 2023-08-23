import { Context } from "../context"
import { Division } from "./types"

export class LeagueApi {
  constructor(private context: Context) {}

  /**
   * Fetches all pairings of the league.
   */
  async fetchPairings(): Promise<Array<Division>> {
    return this.fetchApi('unstable/pairings/')
  }

  private async fetchApi(endpoint: string): Promise<any> {
    const response = await fetch(this.baseUrl() + 'api/' + endpoint)
    return await response.json()
  }
  
  /**
   * Returns the base URL for the current league.
   */
  private baseUrl(): string {
    const path = this.context.currentPath.split('/')
    return `/${path[1]}/${path[2]}/`
  }
}
