import { Component, Input, TemplateRef } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PlayerData, PlayerDataComponent } from './player-data/player-data.component';
import { JsonPipe } from '@angular/common';
import { Config } from './types';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [PlayerDataComponent, JsonPipe],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent {
  @Input({alias: "config"}) configString: string | undefined

  playerData: PlayerData | null = null

  constructor(private modalService: NgbModal) {
  }

  openRegistration(content: TemplateRef<any>) {
    this.modalService.open(content).result.then(
      (result) => {
        console.log(`Closed with: ${result}`)
      }
    )
  }

  get config(): Config {
    return JSON.parse(this.configString!)
  }
}
