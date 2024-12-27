import { Component, Input, OnInit } from '@angular/core';
import { Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog/dialog.service';
import { Tournament } from './tournament';
import { RegistrationService } from './registration.service';
import { CommonModule } from '@angular/common';
import { NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [NgbTooltipModule, CommonModule],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent implements OnInit {
  @Input({alias: "config"}) configString: string
  @Input({alias: "players"}) playersString: string
  @Input({alias: "manager"}) isManager: boolean

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
    const player = await this.dialogService.open<PlayerDialogParams>(PlayerDialogComponent, {
      tournament: this.tournament!,
      isManager: this.isManager,
      lastPlayer: this.registeredPlayers.slice(-1)[0]
    }).result;
    this.registeredPlayers.push(player)
    this.reloadPlayerList()
  }

  async editPlayer(player: Player) {
    await this.dialogService.open<PlayerDialogParams>(PlayerDialogComponent, {
      tournament: this.tournament!,
      isManager: this.isManager,
      player
    }).result;
    this.reloadPlayerList()
  }

  async deletePlayer(player: Player) {
    this.dialogService.confirm({
      title: "Spieler löschen",
      message: `${player.playerData.name} wirklich löschen?`,
      confirmText: "Löschen",
      onConfirm: async () => {
        await this.registrationService.deletePlayer(this.tournament!.config.id, player.id)
        this.reloadPlayerList()
      }
    })
  }

  private async reloadPlayerList() {
    const players = await this.registrationService.players(this.tournament!.config.id)
    this.tournament = new Tournament(this.tournament!.config, players)
  }
}
