import { Component, Input, OnInit, TemplateRef, viewChild } from '@angular/core';
import { Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog/dialog.service';
import { Tournament } from './tournament';
import { RegistrationService } from './registration.service';
import { CommonModule } from '@angular/common';
import { NgbAccordionBody, NgbAccordionButton, NgbAccordionCollapse, NgbAccordionDirective, NgbAccordionHeader, NgbAccordionItem, NgbAccordionToggle, NgbNavModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { NsvTableComponent, TableColumn, TableOptions } from '../core/table/table.component';
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
  confirmedToggleTemplate = viewChild.required<TemplateRef<Player>>('confirmedToggle');
  tableOptions: TableOptions<Player>
  waitlistTableOptions: TableOptions<Player>
  cancelledTableOptions: TableOptions<Player>
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
    this.mayOpenRegistration = this.tournament.registrationStarted && (!this.tournament.deadlinePassed || this.isManager)

    this.tableOptions = this.createTableOptions('anmeldungen', {
      visibility: { unregisteredAt: 'never', confirmed: 'show' },
      defaultSorting: [
        { columnId: 'group', direction: 'asc' },
        { columnId: 'name', direction: 'asc' }
      ]
    })
    this.waitlistTableOptions = this.createTableOptions('warteliste', {
      visibility: { created: 'show', unregisteredAt: 'never' },
      defaultSorting: [{ columnId: 'created', direction: 'asc' }]
    })
    this.cancelledTableOptions = this.createTableOptions('abmeldungen', {
      visibility: { unregisteredAt: 'show' },
      defaultSorting: [{ columnId: 'unregisteredAt', direction: 'desc' }]
    })
  }

  /**
   * Creates table options containing all player columns, including the additional
   * fields of the tournament config. Column visibility can be overridden by column id,
   * e.g. 'never' to remove a column completely.
   */
  private createTableOptions(csvSuffix: string, options: {
    visibility?: Record<string, TableColumn<Player, any>['visibility']>,
    defaultSorting?: TableOptions<Player>['defaultSorting']
  }): TableOptions<Player> {
    const additionalFieldColumns: TableColumn<Player, any>[] = (this.tournament.config.additionalFields || []).map(field => ({
      id: `additionalField-${field.id}`,
      label: field.label,
      valueFn: (player: Player) => player.additionalFields ? (player.additionalFields[field.id] || '') : '',
      visibility: 'hide'
    }))
    const columns: TableColumn<Player, any>[] = [
      { id: 'created', label: 'Angemeldet am', visibility: 'hide' },
      { id: 'unregisteredAt', label: 'Abgemeldet am', valueFn: (player: Player) => player.unregisteredAt ?? '', visibility: 'hide' },
      { id: 'group', label: 'Turnier', valueFn: (player: Player) => player.group },
      { id: 'waitlist', label: 'Warteliste', valueFn: (player: Player) => player.waitlist ? 'Ja' : 'Nein', visibility: 'hide' },
      { id: 'confirmed', label: 'Bestätigt', valueFn: (player: Player) => player.confirmed ? 'Ja' : 'Nein', templateRef: this.confirmedToggleTemplate, visibility: 'hide' },
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
      ...additionalFieldColumns,
      { id: 'id', label: 'Anmeldungs-ID', visibility: 'hide' },
      { id: 'actions', label: '', sortable: false, templateRef: this.playerActionsTemplate, visibility: 'always', skipExport: true }
    ]
    return {
      columns: columns.map(col => {
        const visibility = options.visibility?.[col.id]
        return visibility ? { ...col, visibility } : col
      }),
      idFn: (player: Player) => player.id,
      defaultSorting: options.defaultSorting,
      searchColumns: ['name', 'club'],
      showColumnSelection: true,
      showRowCount: true,
      csvFileName: () => `${this.tournament?.config.id}-${csvSuffix}-${new Date().toISOString().substring(0, 10)}.csv`
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

  async toggleConfirmed(player: Player) {
    await this.registrationService.updatePlayer(this.tournament!.config.id, {...player, confirmed: !player.confirmed})
    this.reloadPlayerList()
  }

  async deletePlayer(player: Player) {
    this.dialogService.confirm({
      title: "Anmeldung abmelden",
      message: `${player.playerData.name} wirklich abmelden?`,
      confirmText: "Abmelden",
      onConfirm: async () => {
        await this.registrationService.deletePlayer(this.tournament!.config.id, player.id)
        this.reloadPlayerList()
      }
    })
  }

  formatDate(dateStr: string): string {
    return new Intl.DateTimeFormat('de-DE', {day: 'numeric', month: 'long'}).format(new Date(dateStr))
  }

  private async reloadPlayerList() {
    const players = await this.registrationService.players(this.tournament!.config.id)
    this.tournament = new Tournament(this.tournament!.config, players)
  }
}
