import { Component, Input, OnInit } from '@angular/core';
import { Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog/dialog.service';
import { Tournament } from './tournament';
import { RegistrationService } from './registration.service';
import { CommonModule } from '@angular/common';
import { NgbNavModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { NsvTableComponent, TableOptions } from '../core/table/table.component';

@Component({
    selector: 'nsv-registration',
    imports: [NgbNavModule, NgbTooltipModule, CommonModule, NsvTableComponent],
    templateUrl: './registration.component.html',
    styleUrl: './registration.component.css'
})
export class RegistrationComponent implements OnInit {
  @Input({alias: "config"}) configString: string
  @Input({alias: "players"}) playersString: string
  @Input({alias: "manager"}) isManager: boolean

  tournament: Tournament
  registeredPlayers: Player[] = []
  activeTab = 3;  // TODO: DO NOT SUBMIT

  readonly INFINITY = Infinity;
  readonly tableOptions: TableOptions<Player> = {
    columns: [
      { id: 'group', label: 'Turnier', valueFn: (player: Player) => player.group },
      { id: 'waitlist', label: 'Warteliste' },
      { id: 'name', label: 'Name', valueFn: (player: Player) => player.playerData.name },
      { id: 'club', label: 'Verein', valueFn: (player: Player) => player.playerData.club },
      { id: 'dwz', label: 'DWZ', valueFn: (player: Player) => player.playerData.dwz },
      { id: 'elo', label: 'Elo', valueFn: (player: Player) => player.playerData.elo },
    ],
    idFn: (player: Player) => player.id,
    defaultSorting: [
      { columnId: 'group', direction: 'asc' },
      { columnId: 'waitlist', direction: 'asc' },
      { columnId: 'name', direction: 'asc' }
    ]
  }

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

  async confirmWaitlistPlayer(player: Player) {
    this.dialogService.confirm({
      title: "In Turnier aufnehmen",
      message: `${player.playerData.name} in das Turnier aufnehmen? Eine Bestätigung wird an ${player.contactDetails.email} gesendet.`,
      confirmText: "Aufnehmen",
      onConfirm: async () => {
        await this.registrationService.updatePlayer(this.tournament!.config.id, {...player, waitlist: false})
        this.reloadPlayerList()
      }
    })
  }

  async deletePlayer(player: Player) {
    this.dialogService.confirm({
      title: "Anmeldung löschen",
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
