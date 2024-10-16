import { Injectable } from '@angular/core';
import { Player } from './types';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

const ENDPOINT = '/v3/anmeldung/api';

@Injectable({
  providedIn: 'root'
})
export class RegistrationService {

  constructor(private http: HttpClient) { }

  registerPlayer(tournamentId: string, player: Player): Observable<void> {
    return this.http.post<any>(`${ENDPOINT}/${tournamentId}/players/`, player)
  }
}
