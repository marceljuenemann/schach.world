import { Component, Injector, Input } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Config, CONFIG_TOKEN } from './types';
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

  constructor(private modalService: NgbModal) {
  }

  async openRegistration() {
    // TODO: Make scrollable within the dialog
    // TODO: Probably wrap this in a nicer dialog service?
    const dialog = this.modalService.open(PlayerDialogComponent, {
      injector: Injector.create({providers: [{
        provide: CONFIG_TOKEN, useValue: this.config
      }]})
    })
    const result = await dialog.result
    console.log(`Closed with: ${result}`)
  }

  get config(): Config {
    return JSON.parse(this.configString!)
  }
}
