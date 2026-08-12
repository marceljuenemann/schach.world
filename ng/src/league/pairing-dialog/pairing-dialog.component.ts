import { Component } from '@angular/core';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';

export interface PairingDialogParams {
  pairingId: number
  matchDay: unknown
  divisions: unknown[]
  teams: unknown[]
}

@Component({
    selector: 'league-pairing-dialog',
    imports: [NsvDialogFooterComponent],
    templateUrl: './pairing-dialog.component.html',
    styleUrl: './pairing-dialog.component.css'
})
export class PairingDialog extends NsvDialog<PairingDialogParams> {
  override save(): Promise<void> {
    return Promise.resolve()  // infrastructure only for now - no persistence yet
  }
}
