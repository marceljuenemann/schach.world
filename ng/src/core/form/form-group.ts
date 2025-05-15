import { FormControl, FormGroup, Validators } from "@angular/forms"

/**
 * Extension of FormGroup that also helps with things like
 * transforming values, manging visibility, showing errors.
 */
export class NsvFormGroup<T extends {[K in keyof T]: NsvFormControl<any>} = any> extends FormGroup<T> {
  constructor(public override controls: T) {
    super(controls)
  }

  /**
   * Like `value`, but some NsvFormControls transform values, e.g.
   * IntControl will actually parse the value into an integer.
   */
  get transformedValue() {
    return Object.fromEntries(
      Object.entries(this.controls).map(([key, control]) => {
        return [key, (control as NsvFormControl).transformedValue];
      })
    )
  }
}

export abstract class NsvFormControl<T = any> extends FormControl {
  public abstract readonly label: string

  get transformedValue() {
    return this.value
  }
}

export class TextControl extends NsvFormControl<string> {
  constructor(public readonly label: string, opts: {required?: boolean} = {}) {
    super('', opts.required ? Validators.required : undefined);
  }

  override get transformedValue() {
    return this.value?.trim()
  }
}

export class IntControl extends NsvFormControl<number> {
  constructor(public readonly label: string, opts: {required?: boolean} = {}) {
    const validators = [Validators.pattern("^[0-9]*$")]
    if (opts.required) {
      validators.push(Validators.required)
    }
    super('', validators);
  }

  override get transformedValue() {
    return parseInt(super.transformedValue) || null
  }
}
