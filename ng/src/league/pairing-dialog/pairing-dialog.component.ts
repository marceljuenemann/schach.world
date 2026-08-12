import { Component } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { LeagueService } from '../league.service';
import { MatchDay, Pairing } from '../types';
import { PairingEditor, RESULT_OPTIONS } from './pairing-editor';

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
  sendConfirmation = new FormControl(false)
  resultOptions = RESULT_OPTIONS

  constructor(private leagueService: LeagueService) {
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

  override save(): Promise<void> {
    return Promise.resolve()  // still no persistence - logic/UI only for this step
  }
}
