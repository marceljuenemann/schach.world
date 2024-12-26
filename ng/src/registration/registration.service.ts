import { Injectable } from '@angular/core';
import { Player } from './types';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom, lastValueFrom, Observable } from 'rxjs';

const ENDPOINT = '/v3/anmeldung/api';

@Injectable({
  providedIn: 'root'
})
export class RegistrationService {

  constructor(private http: HttpClient) { }

  players(tournamentId: string): Promise<Player[]> {
    return lastValueFrom(this.http.get<any>(`${ENDPOINT}/${tournamentId}/players/`))
  }

  registerPlayer(tournamentId: string, player: Player): Promise<void> {
    return lastValueFrom(this.http.post<any>(`${ENDPOINT}/${tournamentId}/players/`, player))
  }

  updatePlayer(tournamentId: string, player: Player): Promise<void> {
    return lastValueFrom(this.http.put<any>(`${ENDPOINT}/${tournamentId}/players/${player.id}/`, player))
  }

  deletePlayer(tournamentId: string, playerId: number): Promise<void> {
    return lastValueFrom(this.http.delete<any>(`${ENDPOINT}/${tournamentId}/players/${playerId}/`))
  }
}
