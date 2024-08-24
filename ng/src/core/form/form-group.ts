import { FormControl, FormGroup } from "@angular/forms"

/**
 * Extension of FormGroup that also helps with things like
 * transforming values, manging visibility, showing errors.
 */
export class NsvFormGroup<T extends {[K in keyof T]: NsvFormControl<any>} = any> extends FormGroup<T> {
  constructor(public override controls: T) {
    super(controls)
  }
}

export abstract class NsvFormControl<T = any> extends FormControl {
  public abstract readonly label: string
}

export class TextControl extends NsvFormControl<string> {
  constructor(public readonly label: string) {
    super();
  }
}
