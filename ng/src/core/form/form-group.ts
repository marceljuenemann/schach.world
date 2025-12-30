import { FormControl, FormControlOptions, FormGroup, ValidatorFn, Validators } from "@angular/forms"

type NsvFormType = 'text' | 'int' | 'multiline' | 'select';

/**
 * Configuration options for a form control.
 */
export interface NsvFormConfig {
  type: NsvFormType
  id: string
  label: string
  required: boolean
  options?: {
    label: string
    value: string
    disabled?: boolean
  }[]
}

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

  /**
   * Dynamically adds a control generated from the configuration passed.
   */
  addControls(config: NsvFormConfig[]) {
    for (const cfg of config) {
      switch (cfg.type) {
        case 'text':
          this.addControl(cfg.id, new TextControl(cfg.label, {required: cfg.required}))
          break
        case 'multiline':
          this.addControl(cfg.id, new TextareaControl(cfg.label, {required: cfg.required}))
          break
        case 'int':
          this.addControl(cfg.id, new IntControl(cfg.label, {required: cfg.required}))
          break
        case 'select':
          this.addControl(cfg.id, new SelectControl(cfg.label, cfg.options, {required: cfg.required}))
          break
        default:
          console.warn(`Unsupported control type ${cfg.type}`)
          continue
      }
    }
  }
}

export abstract class NsvFormControl<T = any> extends FormControl {
  constructor(public readonly type: NsvFormType, public readonly label: string, opts: FormControlOptions | ValidatorFn | ValidatorFn[] = {}) {
    super('', opts);
  }

  get transformedValue() {
    return this.value
  }
}

export class TextControl extends NsvFormControl<string> {
  constructor(label: string, opts: {required?: boolean} = {}) {
    super('text', label, opts.required ? Validators.required : undefined)
  }

  override get transformedValue() {
    return this.value?.trim()
  }
}

export class TextareaControl extends NsvFormControl<string> {
  constructor(label: string, opts: {required?: boolean} = {}) {
    super('multiline', label, opts.required ? Validators.required : undefined);
  }

  override get transformedValue() {
    return this.value?.trim()
  }
}

export class IntControl extends NsvFormControl<number> {
  constructor(label: string, opts: {required?: boolean} = {}) {
    const validators = [Validators.pattern("^[0-9]*$")]
    if (opts.required) {
      validators.push(Validators.required)
    }
    super('int', label, validators);
  }

  override get transformedValue() {
    return parseInt(super.transformedValue) || null
  }
}

export class SelectControl extends NsvFormControl<string> {
  constructor(label: string, public readonly options: NsvFormConfig['options'], opts: {required?: boolean} = {}) {
    super('select', label, opts.required ? Validators.required : undefined)
  }

  override get transformedValue() {
    return this.value?.trim()
  }
}
