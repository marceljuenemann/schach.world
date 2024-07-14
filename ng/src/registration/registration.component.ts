import { Component, TemplateRef } from '@angular/core';
import { NgbAccordionModule, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PlayerData } from '../dwz/dwz.service';
import { PlayerDataComponent } from './player-data/player-data.component';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [PlayerDataComponent],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent {

  constructor(private modalService: NgbModal) {}

  openRegistration(content: TemplateRef<any>) {
    this.modalService.open(content).result.then(
      (result) => {
        console.log(`Closed with: ${result}`)
      }
    )
  }

  selected(player: PlayerData|undefined) {
    console.log(player)
  }
}
