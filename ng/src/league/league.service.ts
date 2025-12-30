import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { lastValueFrom } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LeagueService {

  constructor(private http: HttpClient) { }

  updateTeamNameAndNumber(teamId: number, data: {name: string, number: number}): Promise<void> {
    return this.put(`/teams/${teamId}/updateNameAndNumber/`, data)
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
}
