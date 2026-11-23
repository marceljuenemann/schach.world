import { Component } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { NsvDialog } from '../../core/dialog/dialog';
import { DialogService } from '../../core/dialog/dialog.service';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { LeagueService } from '../league.service';
import { LateRegistration, MatchDay, Pairing } from '../types';
import { LateRegistrationDialog, LateRegistrationDialogParams } from './late-registration-dialog.component';
import { BoardRow, lateRegistrationName, LATE_REGISTRATION_OPTION, PairingEditor, PlayerControl, RESULT_OPTIONS } from './pairing-editor';

export interface PairingDialogParams {
  pairingId: number
  matchDay: MatchDay
  divisions: unknown[]
  teams: unknown[]
  boardCount: number
}

@Component({
    selector: 'league-pairing-dialog',
    imports: [ReactiveFormsModule, NsvDialogFooterComponent],
    templateUrl: './pairing-dialog.component.html',
    styleUrl: './pairing-dialog.component.css'
})
export class PairingDialog extends NsvDialog<PairingDialogParams> {
  pairing: Pairing
  loading = true
  editor: PairingEditor
  comment = new FormControl('')
  sendConfirmation = new FormControl(true)
  resultOptions = RESULT_OPTIONS
  lateRegistrationOption = LATE_REGISTRATION_OPTION
  lateRegistrationName = lateRegistrationName

  constructor(private leagueService: LeagueService, private dialogService: DialogService) {
    super()
    this.pairing = this.params.matchDay.pairings.find(p => p.id === this.params.pairingId)!
    this.comment.setValue(this.pairing.comment ?? '')
    this.loadRosters()
  }

  private async loadRosters() {
    const [team1, team2] = await Promise.all([
      this.leagueService.getTeam(this.pairing.team1.id),
      this.leagueService.getTeam(this.pairing.team2.id),
    ])
    this.editor = new PairingEditor(this.pairing, this.params.boardCount, team1, team2)
    this.loading = false
  }

  override get isValid() {
    return !this.loading
  }

  override save(): Promise<void> {
    const request = this.editor.toRequest(this.comment.value ?? '', this.sendConfirmation.value ?? false)
    return this.leagueService.updatePairing(this.pairing.id, request)
  }

  // The dialog has no form fields that could show the server's validation errors.
  get validationMessages(): string[] {
    return Object.values(this.errors?.validationErrors ?? {}).flat().map(error => error.message)
  }

  onPlayerChange(row: BoardRow, control: PlayerControl) {
    if (control.value === LATE_REGISTRATION_OPTION) {
      this.openLateRegistrationDialog(row, control)
    } else {
      this.editor.onPlayerSelected(row, control)
    }
  }

  private openLateRegistrationDialog(row: BoardRow, control: PlayerControl) {
    const previousValue = control.previousValue
    const isFirstTeam = control === row.player1
    const team = isFirstTeam ? this.editor.team1 : this.editor.team2
    const groups = isFirstTeam ? this.editor.rosterGroups1 : this.editor.rosterGroups2
    const params: LateRegistrationDialogParams = {
      teamName: team.name,
      teamNumbers: groups.map(group => group.teamNumber),
      preferredZps: team.zps,
    }
    this.dialogService.open(LateRegistrationDialog, params).result.then(
      (lateRegistration: LateRegistration) => this.editor.addLateRegistration(row, control, lateRegistration),
      () => control.setValue(previousValue),
    )
  }
}
