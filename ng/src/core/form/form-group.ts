import { FormControl, FormGroup, Validators } from "@angular/forms"

/**
 * Extension of FormGroup that also helps with things like
 * transforming values, manging visibility, showing errors.
 */
export class NsvFormGroup<T extends {[K in keyof T]: NsvFormControl<any>} = any> extends FormGroup<T> {
  constructor(public override controls: T) {
    super(controls)
  }

  hideControls() {
    for (const control of Object.values(this.controls)) {
      (control as NsvFormControl).visible = false
    }
  }

  showControls() {
    for (const control of Object.values(this.controls)) {
      (control as NsvFormControl).visible = true
    }
  }
}

export abstract class NsvFormControl<T = any> extends FormControl {
  public abstract readonly label: string
  public visible: boolean = true
}

export class TextControl extends NsvFormControl<string> {
  constructor(public readonly label: string, opts: {required?: boolean} = {}) {
    super('', opts.required ? Validators.required : undefined);
  }
}

export class IntControl extends NsvFormControl<number> {
  constructor(public readonly label: string, opts: {required?: boolean} = {}) {
    super('', opts.required ? Validators.required : undefined);
  }
}
