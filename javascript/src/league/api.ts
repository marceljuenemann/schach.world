import { NsvApi } from "../core/api";
import { Division } from "./types"

export class LeagueApi extends NsvApi {

  async fetchPairings(): Promise<Array<Division>> {
    return this.request('unstable/pairings/')
  }

  async createDivision(division: Record<string, any>): Promise<void> {
    return this.request('divisions/create/', 'POST', division)
  }

  async updateDivisionSortOrder(divisionIds: Array<number>): Promise<void> {
    return this.request('divisions/order/', 'PUT', {divisionIds})
  }

  protected async request<T>(url: string, method: string = 'GET', body: any = null): Promise<T> {
    return super.request(this.baseUrl() + 'api/' + url, method, body)
  }
  
  /**
   * Returns the base URL for the current league.
   */
  private baseUrl(): string {
    const path = this.context.currentPath.split('/')
    return `/${path[1]}/${path[2]}/`
  }
}
