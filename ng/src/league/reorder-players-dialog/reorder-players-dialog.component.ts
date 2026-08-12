import { Component } from '@angular/core';
import { CdkDropList, CdkDrag, CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { LeagueService } from '../league.service';

export interface ReorderPlayer {
  id: number
  number: number
  name: string
}

export interface ReorderPlayersDialogParams {
  teamNumber: number
  firstPlayerNumber: number
  team: {
    id: number
    playersByTeamNumber: Record<number, ReorderPlayer[]>
  }
}

@Component({
    selector: 'league-reorder-players-dialog',
    imports: [CdkDropList, CdkDrag, NsvDialogFooterComponent],
    templateUrl: './reorder-players-dialog.component.html',
    styleUrl: './reorder-players-dialog.component.css'
})
export class ReorderPlayersDialog extends NsvDialog<ReorderPlayersDialogParams> {
  orderedPlayers: ReorderPlayer[]

  constructor(private leagueService: LeagueService) {
    super()
    this.orderedPlayers = [...this.params.team.playersByTeamNumber[this.params.teamNumber]]
  }

  drop(event: CdkDragDrop<ReorderPlayer[]>) {
    moveItemInArray(this.orderedPlayers, event.previousIndex, event.currentIndex)
  }

  override save(): Promise<void> {
    return this.leagueService.reorderPlayers(this.params.team.id, this.orderedPlayers.map(p => p.id))
  }
}
