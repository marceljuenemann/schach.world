import { Component, Input, OnInit, TemplateRef, viewChild } from '@angular/core';
import { Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog/dialog.service';
import { Tournament } from './tournament';
import { RegistrationService } from './registration.service';
import { CommonModule } from '@angular/common';
import { NgbAccordionBody, NgbAccordionButton, NgbAccordionCollapse, NgbAccordionDirective, NgbAccordionHeader, NgbAccordionItem, NgbAccordionToggle, NgbNavModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { NsvTableComponent, TableOptions } from '../core/table/table.component';
import { SwissChessComponent } from './swiss-chess/swiss-chess.component';

@Component({
    selector: 'nsv-registration',
    imports: [
      CommonModule,
      NgbAccordionBody,
      NgbAccordionButton,
      NgbAccordionDirective,
      NgbAccordionHeader,
      NgbAccordionItem,
      NgbAccordionToggle,
      NgbAccordionCollapse,
      NgbNavModule,
      NgbTooltipModule,
      NsvTableComponent,
      SwissChessComponent
    ],
    templateUrl: './registration.component.html',
    styleUrl: './registration.component.css'
})
export class RegistrationComponent implements OnInit {
  INFINITY = Infinity;

  @Input({alias: "config"}) configString: string
  @Input({alias: "players"}) playersString: string
  @Input({alias: "manager"}) isManager: boolean

  tournament: Tournament
  registeredPlayers: Player[] = []
  activeTab = 1;
  mayOpenRegistration: boolean;

  playerNameTemplate = viewChild.required<TemplateRef<Player>>('playerName');
  playerActionsTemplate = viewChild.required<TemplateRef<Player>>('playerActions');
  tableOptions: TableOptions<Player> = {
    columns: [
      { id: 'created', label: 'Datum', visibility: 'hide' },
      { id: 'group', label: 'Turnier', valueFn: (player: Player) => player.group },
      { id: 'waitlist', label: 'Warteliste', valueFn: (player: Player) => player.waitlist ? 'Ja' : 'Nein' },
      { id: 'name', label: 'Name', valueFn: (player: Player) => player.playerData.name, visibility: 'always', templateRef: this.playerNameTemplate },
      { id: 'club', label: 'Verein', responsiveBelow: 'name', valueFn: (player: Player) => player.playerData.club },
      { id: 'gender', label: 'Geschlecht', valueFn: (player: Player) => player.playerData.gender, visibility: 'hide' },
      { id: 'yearOfBirth', label: 'Geburtsjahr', valueFn: (player: Player) => player.playerData.yearOfBirth, defaultSortDirection: 'desc', visibility: 'hide' },
      { id: 'dwz', label: 'DWZ', valueFn: (player: Player) => player.playerData.dwz, defaultSortDirection: 'desc' },
      { id: 'elo', label: 'ELO', valueFn: (player: Player) => player.playerData.elo, defaultSortDirection: 'desc' },
      { id: 'zps', label: 'ZPS', valueFn: (player: Player) => player.playerData.zps ? `${player.playerData.zps}-${player.playerData.memberId}` : '', visibility: 'hide' },
      { id: 'fideId', label: 'FIDE-ID', valueFn: (player: Player) => player.playerData.fideId, visibility: 'hide' },
      { id: 'contactName', label: 'Kontaktname', valueFn: (player: Player) => player.contactDetails.name, visibility: 'hide' },
      { id: 'contactMail', label: 'E-Mail', valueFn: (player: Player) => player.contactDetails.email, visibility: 'hide' },
      { id: 'id', label: 'Anmeldungs-ID', visibility: 'hide' },
      { id: 'actions', label: '', sortable: false, templateRef: this.playerActionsTemplate, visibility: 'always', skipExport: true }
    ],
    idFn: (player: Player) => player.id,
    defaultSorting: [
      { columnId: 'group', direction: 'asc' },
      { columnId: 'waitlist', direction: 'asc' },
      { columnId: 'name', direction: 'asc' }
    ],
    searchColumns: ['name', 'club'],
    showColumnSelection: true,
    showRowCount: true,
    csvFileName: () => `${this.tournament?.config.id}-${new Date().toISOString().substring(0, 10)}.csv`
  }
  waitlistTableOptions: TableOptions<Player> = {
    columns: this.tableOptions.columns.map(col => {
      if (col.id == 'created') return { ...col, visibility: 'show' }
      if (col.id == 'waitlist') return { ...col, visibility: 'never' }
      return col
    }),
    idFn: this.tableOptions.idFn,
    defaultSorting: [{ columnId: 'created', direction: 'asc' }],
    searchColumns: ['name', 'club'],
    showColumnSelection: true,
    showRowCount: true
  }
  overviewTableOptions: TableOptions<Player> = {
    columns: [
      { id: 'name', label: 'Name', valueFn: (player: Player) => player.playerData.name, visibility: 'always', templateRef: this.playerNameTemplate },
      { id: 'club', label: 'Verein', responsiveBelow: 'name', valueFn: (player: Player) => player.playerData.club },
      { id: 'dwz', label: 'DWZ', valueFn: (player: Player) => player.playerData.dwz, defaultSortDirection: 'desc' },
      { id: 'elo', label: 'Elo', valueFn: (player: Player) => player.playerData.elo, defaultSortDirection: 'desc' },
      { id: 'actions', label: '', sortable: false, templateRef: this.playerActionsTemplate, visibility: 'always' }
    ],
    idFn: (player: Player) => player.id,
    defaultSorting: [{ columnId: 'dwz', direction: 'desc' }],
    searchColumns: ['name', 'club'],
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
    this.mayOpenRegistration = !this.tournament.deadlinePassed || this.isManager
    for (const field of this.tournament.config.additionalFields || []) {
      this.tableOptions.columns.splice(this.tableOptions.columns.length - 2, 0, {
        id: `additionalField-${field.id}`,
        label: field.label,
        valueFn: (player: Player) => player.additionalFields ? (player.additionalFields[field.id] || '') : '',
        visibility: 'hide'
      })
    }
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
