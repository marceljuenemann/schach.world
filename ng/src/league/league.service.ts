import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { lastValueFrom } from 'rxjs';

export interface CreateOrUpdatePlayerData {
  firstName: string
  lastName: string
  title: string
  zps: string
  dwz: number | null
  elo: number | null
  yearOfBirth: number | null
  gender: string
  lateRegistrationRound: number | null
}

@Injectable({
  providedIn: 'root'
})
export class LeagueService {

  constructor(private http: HttpClient) { }

  updateTeamNameAndNumber(teamId: number, data: {name: string, number: number}): Promise<void> {
    return this.put(`/teams/${teamId}/updateNameAndNumber/`, data)
  }

  createPlayer(teamId: number, data: CreateOrUpdatePlayerData): Promise<void> {
    return this.post(`/teams/${teamId}/players/`, data)
  }

  updatePlayer(teamId: number, playerId: number, data: CreateOrUpdatePlayerData): Promise<void> {
    return this.put(`/teams/${teamId}/players/${playerId}/`, data)
  }

  deletePlayer(teamId: number, playerId: number): Promise<void> {
    return this.delete(`/teams/${teamId}/players/${playerId}/`)
  }

  reorderPlayers(teamId: number, playerIds: number[]): Promise<void> {
    return this.put(`/teams/${teamId}/players/reorder/`, {playerIds})
  }

  /**
   * Returns the base URL for the current league.
   */
  private baseUrl(): string {
    const path = window.location.pathname.split('/')
    return `/${path[1]}/${path[2]}/api`
  }

  private put<T>(url: string, data: any): Promise<T> {
    return lastValueFrom(this.http.put<any>(this.baseUrl() + url, data))
  }

  private post<T>(url: string, data: any): Promise<T> {
    return lastValueFrom(this.http.post<any>(this.baseUrl() + url, data))
  }

  private delete<T>(url: string): Promise<T> {
    return lastValueFrom(this.http.delete<any>(this.baseUrl() + url))
  }
}
