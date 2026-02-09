import { Component, Input, input } from '@angular/core';
import { DialogService } from '../dialog.service';
import { DIALOG_COMPONENTS } from '../../../entrypoints';

/**
 * Launches a dialog on click. Used as entry point into our angular app
 * by PHP-generated pages.
 */
@Component({
    selector: 'nsv-dialog-launcher',
    imports: [],
    templateUrl: './launcher.component.html',
    styleUrl: './launcher.component.css'
})
export class DialogLauncherComponent {
  // Type of the dialog, as defined in DIALOG_COMPONENTS
  @Input() dialog: string;

  // Dialog parameters encoded as JSON.
  @Input() params: string;

  // Whether to reload the page when the dialog was saved.
  @Input({alias: 'on-save'}) onSave: string;

  constructor(private dialogService: DialogService) {}

  async launchDialog() {
    const component = DIALOG_COMPONENTS[this.dialog]
    const params = JSON.parse(this.params)
    const ref = this.dialogService.open(component, params)
    const result = await ref.result

    if (this.onSave === 'reload') {
      window.location.reload()
    }
  }
}
