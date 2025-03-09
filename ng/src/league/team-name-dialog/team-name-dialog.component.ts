import { Component } from '@angular/core';
import { NsvDialog } from '../../core/dialog/dialog';
import { IntControl, NsvFormGroup, TextControl } from '../../core/form/form-group';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';

export interface TeamNameDialogParams {
  id: number
  name: string
  number: number
}

@Component({
  selector: 'team-name-dialog',
  standalone: true,
  imports: [NsvFormComponent, NsvDialogFooterComponent],
  templateUrl: './team-name-dialog.component.html',
  styleUrl: './team-name-dialog.component.css'
})
export class TeamNameDialog extends NsvDialog<TeamNameDialogParams> {

  form = new NsvFormGroup({
    name: new TextControl('Name', {required: true}),
    number: new IntControl('Mannschaftsnummer', {required: true}),
  })

  constructor() {
    super()
    this.form.patchValue(this.params)
  }

  override get isValid() {
    return this.form.valid
  }

  override save(): Promise<void> {
    throw new Error('Method not implemented.');
  }
}
