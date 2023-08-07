import { Division, Pairing } from "./types"

// TODO: class

/**
 * Fetches all pairings of the league.
 */
export async function fetchPairings(): Promise<Array<Division>> {
  return fetchApi('unstable/pairings/')
}

async function fetchApi(endpoint: string): Promise<any> {
  const baseUrl = "https://localhost/ligen/test-2022/api/"
  const response = await fetch(baseUrl + endpoint)
  return await response.json()
}
