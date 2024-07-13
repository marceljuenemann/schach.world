import { Component, TemplateRef } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PlayerSearchComponent } from '../dwz/player-search/player-search.component';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [PlayerSearchComponent],
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
}
