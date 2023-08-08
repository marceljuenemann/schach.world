import React, { ReactElement } from "react"
import { FloatingLabel, Form } from "react-bootstrap"

export interface FormFieldError {
  message: string
}

export type FormValidationResult = Record<string, Array<FormFieldError>>

/**
 * Utility component for building beautiful, consistent forms without the boilerplate.
 */
export class NsvForm extends React.Component<{
    children: (form: NsvForm) => ReactElement,
    values: Record<string, any>,
    onChange: (values: Record<string, any>) => void,
    validationResult?: FormValidationResult
  }> {

  onFieldChange(id: string, value: any): void {
    const values = {...this.props.values}
    values[id] = value
    this.props.onChange(values)
  }

  render() {
    return this.props.children(this)
  }

  /**
   * A form control with floating label that stores its state on an NsvForm instance.
   */
  static Control = class extends React.Component<{
    form: NsvForm,
    id: string,
    label: string
  }> {
    constructor(props: any) {
      super(props)
    }

    render() {
      const errors = this.validationResult && this.validationResult[this.props.id]
      return (
        <FloatingLabel
          controlId={"nsv-input-" + this.props.id}
          label={this.props.label}
          className="mb-3"
        >
          <Form.Control
            type="text"
            placeholder={this.props.label}
            value={this.props.form.props.values[this.props.id] || ''}
            onChange={e => this.props.form.onFieldChange(this.props.id, e.target.value)}
            isValid={this.validationResult && !errors}
            isInvalid={!!errors}
          />
          { errors && errors.map(error => (
            <Form.Control.Feedback type="invalid">{error.message}</Form.Control.Feedback>
          ))}
        </FloatingLabel>
      )
    }

    private get form(): NsvForm {
      return this.props.form
    }

    private get validationResult(): FormValidationResult|undefined {
      return this.form.props.validationResult
    }
  }
}