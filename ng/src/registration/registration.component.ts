import { Component, Input, OnInit } from '@angular/core';
import { Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog.service';
import { Tournament } from './tournament';
import { RegistrationService } from './registration.service';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent implements OnInit {
  @Input({alias: "config"}) configString: string
  @Input({alias: "players"}) playersString: string

  tournament: Tournament | null = null
  registeredPlayers: Player[] = []

  constructor(
    private dialogService: DialogService,
    private registrationService: RegistrationService
  ) {}

  ngOnInit() {
    this.tournament = new Tournament(
      JSON.parse(this.configString),
      JSON.parse(this.playersString)
    )
  }

  async openRegistration() {
    // TODO: Make scrollable within the dialog
    const player = await this.dialogService.open<PlayerDialogParams>(PlayerDialogComponent, {
      tournament: this.tournament!,
      lastPlayer: this.registeredPlayers.slice(-1)[0]
    }).result;
    this.registeredPlayers.push(player)

    // Reload player list.
    const players = await this.registrationService.players(this.tournament!.config.id)
    this.tournament = new Tournament(this.tournament!.config, players)
  }
}
