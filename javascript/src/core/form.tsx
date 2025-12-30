import React, { ReactElement, ReactNode } from "react"
import { FloatingLabel, Form } from "react-bootstrap"
import { ValidationErrors } from "./api"

export type NsvFormProps<R = void> = {
  // Callback to register a save handler with the parent component.
  onSave: (saveHandler: () => Promise<R>) => void,
  // Callback to trigger a save action.
  triggerSave: () => void,
  validationErrors?: ValidationErrors
}

/**
 * Utility component for building beautiful and consistent forms without the boilerplate.
 */
export abstract class NsvForm<P = {}, R = void> extends React.Component<
  P & NsvFormProps<R>,
  {values: Record<string, any>}
> {
  constructor(props: P & NsvFormProps<R>) {
    super(props)
    this.state = {values: {}}
    this.props.onSave(() => this.save())
  }

  /**
   * Save handler to be implemented by the extending child class.
   */
  abstract save(): Promise<R>

  get values(): Record<string, any> {
    return this.state.values
  }

  onFieldChange(id: string, value: any): void {
    const values = {...this.state.values}
    values[id] = value
    this.setState({values})
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
    private suggestedPassword = Math.random().toString(36).substring(2, 12);  // TODO: move to utils

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
            value={this.props.form.state.values[this.props.id] || ''}
            onChange={e => this.props.form.onFieldChange(this.props.id, e.target.value)}
            onKeyDown={e => { if (e.key === 'Enter') this.props.form.props.triggerSave() }}
            isValid={this.formErrors && !this.errors.length}
            isInvalid={this.errors.length > 0}
          />
          {
            this.errors.map(error => <Form.Control.Feedback type="invalid" key={error.message}>{error.message}</Form.Control.Feedback>)
          }
          {
            this.props.type === 'password' && <Form.Text muted>Passwortvorschlag: {this.suggestedPassword}</Form.Text>
          }
        </FloatingLabel>
      )
    }

    private get formErrors(): ValidationErrors|undefined {
      return this.props.form.props.validationErrors
    }
    
    private get errors(): Array<{message: string}> {
      return (this.formErrors && this.formErrors[this.props.id]) || []
    }
  }
}
