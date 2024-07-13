import { Component, TemplateRef } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'nsv-registration',
  standalone: true,
  imports: [],
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
