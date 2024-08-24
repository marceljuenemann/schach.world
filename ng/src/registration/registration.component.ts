import { Component, Injector, Input } from '@angular/core';
import { Config, GroupConfig, Player } from './types';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog/player-dialog.component';
import { DialogService } from '../core/dialog.service';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent {
  @Input({alias: "config"}) configString: string | undefined

  players: Player[] = []
  lastPlayer: Player | null = null

  constructor(private dialogService: DialogService) {}

  async openRegistration() {
    // TODO: Make scrollable within the dialog
    this.dialogService.open<PlayerDialogParams>(PlayerDialogComponent, {
      config: this.config,
      lastPlayer: this.lastPlayer
    }).result.then(result => {
      this.players.push(result)
      this.lastPlayer = result
    })
  }

  get config(): Config {
    let config = JSON.parse(this.configString!)
    config.groups = new Map(config.groups.map((g: GroupConfig) => [g.id, g]));
    return config
  }
}
