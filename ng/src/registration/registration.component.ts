import { Component, TemplateRef } from '@angular/core';
import { NgbAccordionModule, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DwzPlayer } from '../dwz/dwz.service';
import { PlayerData, PlayerDataComponent } from './player-data/player-data.component';
import { JsonPipe } from '@angular/common';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [PlayerDataComponent, JsonPipe],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css'
})
export class RegistrationComponent {
  playerData: PlayerData | null = null

  constructor(private modalService: NgbModal) {}

  openRegistration(content: TemplateRef<any>) {
    this.modalService.open(content).result.then(
      (result) => {
        console.log(`Closed with: ${result}`)
      }
    )
  }
}
