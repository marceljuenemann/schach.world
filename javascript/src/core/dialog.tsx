import { Alert, Button, Modal } from "react-bootstrap";
import { NsvComponent } from "./component";
import { Context } from "./context";
import { ReactNode } from "react";
import { ApiError } from "./api";

export class DialogContext extends Context {
  public onClose: (value: any) => void = () => {}
}

/**
 * Abstract dialog component using a DialogContext and showing a Close button. 
 */
export abstract class NsvDialog<S = {}, P = {}> extends NsvComponent<S & {title: string}, P & {context: DialogContext}> {

  close(value: any = null): void {
    this.props.context.onClose(value) 
  }

  protected abstract renderBody(): ReactNode

  protected renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        <Button onClick={() => this.close()}>Schlie&szlig;en</Button>
      </Modal.Footer>
    );
  }

  render() {
    return (
      <Modal
        show={true}
        onHide={() => this.close()}
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
export abstract class NsvSaveDialog<S extends NsvSaveDialogState = NsvSaveDialogState, T = void, P = {}> extends NsvDialog<S, P> {

  protected abstract save(): Promise<T>

  private onSave() {
    this.save().then(
      result => this.close(result),
      error => this.setState({saveError: ApiError.from(error)})
    )
  }

  protected override renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        {
          this.state.saveError && this.state.saveError.messages.map(message => {
            return <Alert variant='danger' className="w-100">{ message }</Alert>
          })
        }
        <Button variant="secondary" onClick={() => this.close()}>Abbrechen</Button>
        <Button variant="primary" onClick={() => this.onSave()}>Speichern</Button>
      </Modal.Footer>
    );
  }
}
