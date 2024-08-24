import { Component, Input } from '@angular/core';
import { NsvFormControl, NsvFormGroup } from './form-group';
import { ReactiveFormsModule } from '@angular/forms';

/**
 * Automatically displays form inputs based on an NsvFormGroup.
 *
 * While this component reduces control over how exactly a form is
 * rendered, this is intended as we want all forms to have a consistent
 * look and feel. The intention is to also make creation of forms very simple.
 */
@Component({
  selector: 'nsv-form',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './form.component.html',
  styleUrl: './form.component.css'
})
export class NsvFormComponent {
  // The NsvFormGroup used for managing state.
  @Input({required: true}) form: NsvFormGroup

  // Number of columns to show the inputs in
  @Input() columns: number

  // TODO: simplify with @let in angular 18?
  get visibleControls(): Array<{id: string, control: NsvFormControl}> {
    //return ['yearOfBirth', 'gender']
    return Object.entries(this.form.controls).map(([id, control]) => {
      return {id, control} as any
    })
  }

  get colClass() {
    return 'col-' + (12 / (this.columns || 1))
  }
}
