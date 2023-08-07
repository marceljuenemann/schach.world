import { Button, Modal } from "react-bootstrap";
import { NsvComponent } from "./component";
import { Context } from "./context";
import { ReactNode } from "react";

export class DialogContext extends Context {
  public onClose: (value: any) => void = () => {}
}

/**
 * Abstract dialog component using a DialogContext and showing a Close button. 
 */
export abstract class NsvDialog<S = {title: string}, P = {context: DialogContext}> extends NsvComponent<S & {title: string}, P & {context: DialogContext}> {

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

/**
 * Abstract dialog with Save and Cancel buttons. 
 */
export abstract class NsvSaveDialog<S = {title: string}, T = boolean, P = {context: DialogContext}> extends NsvDialog<S, P> {

  protected abstract save(): T

  private onSave() {
    const result = this.save()
    if (result) {
      this.close(result)
    }
  }

  protected override renderFooter(): ReactNode {
    return (
      <Modal.Footer>
        <Button variant="secondary" onClick={() => this.close()}>Abbrechen</Button>
        <Button variant="primary" onClick={() => this.onSave()}>Speichern</Button>
      </Modal.Footer>
    );
  }
}
