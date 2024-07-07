import { Alert, Button, Modal } from "react-bootstrap";
import React, { ReactElement, ReactNode } from "react";
import ReactDOM from 'react-dom/client';
import { ApiError } from "./api";
import { NsvFormProps } from "./form";
import { LoadingComponent } from "./loader";

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
            triggerSave: () => this.onSave(),
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
 * Dialog with a LoadingComponent as body.
 */
export abstract class NsvLoadingDialog<L = {}, P = {}, R = void> extends NsvDialog<P, R> {
 
  abstract loadProps(): Promise<L>
  abstract renderBodyWithProps(props: L & NsvFormProps<R>): ReactNode

  renderBody(formProps: NsvFormProps<R>): ReactNode {
    return <NsvLoadingDialog.DelegatingLoader dialog={this} formProps={formProps}></NsvLoadingDialog.DelegatingLoader>
  }

  static DelegatingLoader = class<L, R> extends LoadingComponent<L, {dialog: NsvLoadingDialog<L, any, R>, formProps: NsvFormProps<R>}> {
    loadProps = () => this.props.dialog.loadProps()
    renderWithProps(props: L) {
      return this.props.dialog.renderBodyWithProps({...this.props.formProps, ...props})
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
