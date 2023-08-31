import { Alert, Button, Modal } from "react-bootstrap";
import React, { ReactElement, ReactNode } from "react";
import ReactDOM from 'react-dom/client';
import { ApiError } from "./api";

/**
 * Abstract dialog component showing a title and close button.
 */
export abstract class NsvDialog<S = {}, R = void, P = {}> extends React.Component<P & {
    onClose: (result?: R) => void
  }, S & {title: string}> {

  protected abstract renderBody(): ReactNode

  protected renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        <Button onClick={() => this.props.onClose()}>Schlie&szlig;en</Button>
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
          <Modal.Title id="modal-title">
            { this.state.title }
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          { this.renderBody() }
        </Modal.Body>
        { this.renderFooter() }
      </Modal>
    );
  }
}

export interface NsvSaveDialogState {
  saveError?: ApiError
}

/**
 * Abstract dialog with Save and Cancel buttons. 
 */
export abstract class NsvSaveDialog<S extends NsvSaveDialogState = NsvSaveDialogState, R = boolean, P = {}> extends NsvDialog<S, R, P> {

  protected abstract save(): Promise<R>

  private onSave() {
    this.save().then(
      result => this.props.onClose(result),
      error => this.setState({saveError: ApiError.from(error)})
    )
  }

  protected override renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        {
          this.state.saveError && this.state.saveError.messages.map((message, i) => {
            return <Alert key={i} variant='danger' className="w-100">{ message }</Alert>
          })
        }
        <Button variant="secondary" onClick={() => this.props.onClose()}>Abbrechen</Button>
        <Button variant="primary" onClick={() => this.onSave()}>Speichern</Button>
      </Modal.Footer>
    );
  }
}

/**
 * Launches a dialog by creating a new React root element in the DOM.
 * 
 * @param dialogFactory factory for creating the dialog component, given the onClose handler
 * @returns a promise that will resolve once the dialog was closed again. It
 * will resolve to the value returned by the dialog, which will be undefined
 * if the dialog was closed without saving.
 */
export function launchDialog<R>(dialogFactory: (onClose: (result?: R) => void) => ReactElement): Promise<R|undefined> {
  return new Promise(resolve => {
    // Create a new root element in the DOM.
    const container = $("<div>")
    $("body").append(container);
    const root = ReactDOM.createRoot(container[0])

    // onClose: Remove root element again and resolve Promise.
    const onClose = (result?: R) => {
      root.unmount()
      container.remove()
      resolve(result)
    }

    // Launch dialog.
    root.render(dialogFactory(onClose));
  })
}
