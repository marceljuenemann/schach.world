import React, { ReactElement } from "react"
import { FloatingLabel, Form } from "react-bootstrap"
import { ValidationErrors } from "./api"

/**
 * Utility component for building beautiful, consistent forms without the boilerplate.
 */
export class NsvForm extends React.Component<{
    children: (form: NsvForm) => ReactElement,
    values: Record<string, any>,
    onChange: (values: Record<string, any>) => void,
    validationErrors?: ValidationErrors
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
   * A form control with floating label and validation state.
   */
  static Control = class extends React.Component<{
    form: NsvForm,
    id: string,
    label: string,
    type?: string
  }> {
    constructor(props: any) {
      super(props)
    }

    render() {
      return (
        <FloatingLabel
          controlId={"nsv-input-" + this.props.id}
          label={this.props.label}
          className="mb-3"
        >
          <Form.Control
            type={this.props.type || "text"}
            placeholder={this.props.label}
            value={this.props.form.props.values[this.props.id] || ''}
            onChange={e => this.props.form.onFieldChange(this.props.id, e.target.value)}
            isValid={this.formErrors && !this.errors.length}
            isInvalid={this.errors.length > 0}
          />
          { this.errors.map(error => (
            <Form.Control.Feedback type="invalid">{error.message}</Form.Control.Feedback>
          ))}
        </FloatingLabel>
      )
    }

    private get form(): NsvForm {
      return this.props.form
    }

    private get formErrors(): ValidationErrors|undefined {
      return this.form.props.validationErrors
    }
    
    private get errors(): Array<{message: string}> {
      let errors = this.formErrors && this.formErrors[this.props.id]
      return errors || []
    }
  }
}
