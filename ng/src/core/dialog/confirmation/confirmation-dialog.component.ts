import { Component } from '@angular/core';
import { NsvDialog } from '../dialog';
import { NsvDialogFooterComponent } from '../footer/dialog-footer.component';

export interface ConfirmationDialogParams<T> {
  title: string
  message: string
  confirmText: string
  onConfirm: () => Promise<T>
}

@Component({
    selector: 'confirmation-dialog',
    imports: [NsvDialogFooterComponent],
    templateUrl: './confirmation-dialog.component.html',
    styleUrl: './confirmation-dialog.component.css'
})
export class ConfirmationDialogComponent<T> extends NsvDialog<ConfirmationDialogParams<T>, T> {
  constructor() {
    super()
  }

  override save(): Promise<T> {
    return this.params.onConfirm()
  }
}
