import { Component, Input } from '@angular/core';
import { NsvDialog } from '../dialog';

@Component({
  selector: 'nsv-dialog-footer',
  standalone: true,
  imports: [],
  templateUrl: './dialog-footer.component.html',
  styleUrl: './dialog-footer.component.css'
})
export class NsvDialogFooterComponent {
  @Input() dialog: NsvDialog<any, any>
  @Input() saveButtonLabel: string | undefined
}
