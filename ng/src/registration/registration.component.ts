import { Component, Injector, Input } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Config, CONFIG_TOKEN, GroupConfig, Player } from './types';
import { PlayerDialogComponent } from './player-dialog/player-dialog.component';

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

  constructor(private modalService: NgbModal) {
  }

  async openRegistration() {
    // TODO: Make scrollable within the dialog
    // TODO: Probably wrap this in a nicer dialog service?
    this.modalService.open(PlayerDialogComponent, {
      injector: Injector.create({providers: [{
        provide: CONFIG_TOKEN, useValue: this.config
      }]})
    }).result.then(result => {
      this.players.push(result)
    })
  }

  get config(): Config {
    let config = JSON.parse(this.configString!)
    config.groups = new Map(config.groups.map((g: GroupConfig) => [g.id, g]));
    return config
  }
}
