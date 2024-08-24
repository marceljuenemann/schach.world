import { Component, Input } from '@angular/core';
import { NsvFormGroup } from './form-group';
import { ReactiveFormsModule } from '@angular/forms';
import { ValidationErrors } from '../api';

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

  // ValidationErrors (usually returned by the NSV server)
  @Input() validationErrors: ValidationErrors | undefined = undefined
  @Input() validationErrorPrefix: string = ''

  // Number of columns to show the inputs in
  @Input() columns: number

  get visibleControls() {
    //return ['yearOfBirth', 'gender']
    return Object.keys(this.form.controls)
  }

  validationError(controlId: string): string | null {
    if (!this.validationErrors) return null
    const errors = this.validationErrors[this.validationErrorPrefix + controlId] || []
    return errors.map(e => e.message).join(' ') || null
  }

  get colClass() {
    return 'col-' + (12 / (this.columns || 1))
  }
}
