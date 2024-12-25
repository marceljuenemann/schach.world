import { Component, Injector, Input, OnInit } from '@angular/core';
import { Config, GroupConfig, Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog.service';
import { Tournament } from './tournament';

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
  players: Player[] = []
  lastPlayer: Player | null = null

  constructor(private dialogService: DialogService) {}

  ngOnInit() {
    this.tournament = new Tournament(
      JSON.parse(this.configString),
      JSON.parse(this.playersString)
    )
  }

  async openRegistration() {
    // TODO: Make scrollable within the dialog
    this.dialogService.open<PlayerDialogParams>(PlayerDialogComponent, {
      tournament: this.tournament!,
      lastPlayer: this.lastPlayer
    }).result.then(result => {
      this.players.push(result)
      this.lastPlayer = result
    })
  }
}
