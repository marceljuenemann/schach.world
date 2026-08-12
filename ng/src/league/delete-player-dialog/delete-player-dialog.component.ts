import { Component } from '@angular/core';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { LeagueService } from '../league.service';

export interface DeletePlayerDialogParams {
  teamId: number
  playerId: number
  playerName: string
}

@Component({
    selector: 'league-delete-player-dialog',
    imports: [NsvDialogFooterComponent],
    templateUrl: './delete-player-dialog.component.html',
    styleUrl: './delete-player-dialog.component.css'
})
export class DeletePlayerDialog extends NsvDialog<DeletePlayerDialogParams> {
  constructor(private leagueService: LeagueService) {
    super()
  }

  override save(): Promise<void> {
    return this.leagueService.deletePlayer(this.params.teamId, this.params.playerId)
  }
}
