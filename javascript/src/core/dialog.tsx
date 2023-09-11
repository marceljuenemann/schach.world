import { Alert, Button, Modal } from "react-bootstrap";
import React, { ReactElement, ReactNode } from "react";
import ReactDOM from 'react-dom/client';
import { ApiError } from "./api";
import { NsvFormProps } from "./form";

export type DialogResult<R> = {saved: true, value: R}
export type CloseHandler<R> = (result?: DialogResult<R>) => void

/**
 * Abstract dialog component with save and close buttons.  
 */
export abstract class NsvDialog<P = {}, R = void> extends React.Component<P & {
  onClose: CloseHandler<R>
}, {
  saveHandler?: () => Promise<R>,
  saveError?: ApiError
}> {
  constructor(props: any) {
    super(props)
    this.state = {}
  }

  /**
   * The dialog title.
   */
  abstract title(): string

  /**
   * The body of the dialog. The props contain validation errors and can
   * also be used to register a handler for the save button. Until a
   * save handler has been registered, the save button will be disabled.
   */
  abstract renderBody(props: NsvFormProps<R>): ReactNode

  renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        {
          this.state.saveError && this.state.saveError.messages.map((message, i) => {
            return <Alert key={i} variant='danger' className="w-100">{ message }</Alert>
          })
        }
        <Button variant="secondary" onClick={() => this.props.onClose()}>Abbrechen</Button>
        {/* TODO: fix color of disabled button */}
        <Button variant="primary" onClick={() => this.onSave()} disabled={!this.state.saveHandler}>Speichern</Button>
      </Modal.Footer>
    );
  }

  render() {
    return (
      <Modal
        show={true}
        onHide={() => this.props.onClose()}
        aria-labelledby="modal-title"
        centered
      >
        <Modal.Header closeButton>
          <Modal.Title id="modal-title">{ this.title() }</Modal.Title>
        </Modal.Header>
        <Modal.Body> {
          this.renderBody({
            onSave: saveHandler => this.setState({saveHandler}),
            validationErrors: this.state.saveError?.validationErrors
          })
        } </Modal.Body>
        { this.renderFooter() }
      </Modal>
    );
  }

  private async onSave() {
    if (this.state.saveHandler) {
      this.state.saveHandler().then(
        result => this.props.onClose({saved: true, value: result}),
        error => this.setState({saveError: ApiError.from(error)})
      )
    }
  }
}

/**
 * Launches a dialog by creating a new React root element in the DOM.
 * 
 * @param dialogFactory factory for creating the dialog component, given the onClose handler
 * @returns a promise that will resolve if the dialog was closed. It might contain a DialogResult
 *  if the dialog was saved. 
 */
export function launchDialog<R>(dialogFactory: (onClose: CloseHandler<R>) => ReactElement): Promise<DialogResult<R>|undefined> {
  return new Promise(resolve => {
    // Create a new root element in the DOM.
    const container = $("<div>")
    $("body").append(container);
    const root = ReactDOM.createRoot(container[0])

    // onClose: Remove root element again and resolve Promise.
    const onClose = (result?: DialogResult<R>) => {
      root.unmount()
      container.remove()
      resolve(result)
    }

    // Launch dialog.
    root.render(dialogFactory(onClose));
  })
}
