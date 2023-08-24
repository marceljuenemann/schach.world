import { NsvApi } from "../core/api";
import { Division } from "./types"

export class LeagueApi extends NsvApi {

  /**
   * Fetches all pairings of the league.
   */
  async fetchPairings(): Promise<Array<Division>> {
    return this.request('unstable/pairings/')
  }

  async updateDivisionSortOrder(divisionIds: Array<number>): Promise<void> {
    return this.request('divisions/order/');
  }

  protected async request<T>(url: string): Promise<T> {
    return super.request(this.baseUrl() + 'api/' + url)
  }
  
  /**
   * Returns the base URL for the current league.
   */
  private baseUrl(): string {
    const path = this.context.currentPath.split('/')
    return `/${path[1]}/${path[2]}/`
  }
}
