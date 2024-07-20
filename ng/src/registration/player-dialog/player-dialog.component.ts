import { Component, Inject, inject } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { JsonPipe } from '@angular/common';
import { Config, CONFIG_TOKEN } from '../types';

@Component({
  selector: 'player-dialog',
  standalone: true,
  imports: [PlayerDataComponent, JsonPipe],
  templateUrl: './player-dialog.component.html',
  styleUrl: './player-dialog.component.css'
})
export class PlayerDialogComponent {
  modal = inject(NgbActiveModal)

  playerData: PlayerData | null = null

  constructor(@Inject(CONFIG_TOKEN) public config: Config) {}

}
