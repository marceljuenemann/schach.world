import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

export interface DwzPlayer {
  name: string
  club: string
  zps: string
  memberId: string
  status: 'A' | 'P'
  gender: 'W' | 'M'
  yearOfBirth: number
  dwz: number|null
  elo: number|null
  fideTitle: 'GM' | 'IM' | 'FM' | 'CM' | 'WGM' | 'WIM' | 'WFM' | 'WCM' | null
  fideId: number|null
  fideCountry: string|null
}

export interface DwzClub {
  zps: string
  name: string
}

@Injectable({
  providedIn: 'root'
})
export class DwzService {

  constructor(private http: HttpClient) { }

  /**
   * Searches for players in the DWZ database.
   */
  findPlayer(name: string, preferredZps: string): Observable<DwzPlayer[]> {
    return this.http.get<any>('/dwz/api/players/', {params: {name, preferredZps, active: 1}}).pipe(
      map(players => players.slice(0, 6).map((player: any) => {
        player.name = player.name.replaceAll(',', ', ' )
        player.club = player.club.name
        player.gender = player.gender || 'M'
        if (player.fideTitle && player.fideTitle[0] == 'W') {
          player.fideTitle += 'M'
        }
        return player
      }))
    )
  }

  findClub(name: string, zps: string): Observable<DwzClub[]> {
    return this.http.get<any>('/dwz/api/clubs/', {params: {name, zps}})
  }
}
