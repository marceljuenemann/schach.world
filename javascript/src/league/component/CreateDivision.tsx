import { FloatingLabel, Form, Modal } from "react-bootstrap";
import { NsvSaveDialog } from "../../core/dialog";
import { Division } from "../types";
import { FormValidationResult, NsvForm } from "../../core/form";

/**
 * Dialog for sorting divisions.
 */
export class CreateDivision extends NsvSaveDialog<{
    values: Record<string, any>,
    validation?: FormValidationResult
  }> {
  constructor(props: any) {
    super(props)
    this.state = {
      title: 'Neue Staffel',
      values: {}
    }
  }
  
  renderBody() {
    return (
      <Modal.Body>
        <NsvForm values={this.state.values} onChange={(values) => this.setState({values})} validationResult={this.state.validation}>
          {(form: NsvForm) => (
            <Form>
              <NsvForm.Control form={form} id="name" label="Name" />
              <NsvForm.Control form={form} id="managerName" label="Staffelleiter:in Name" />
            </Form>
          )}
        </NsvForm>
      </Modal.Body>
    );
  }

  save(): boolean {
    console.log("Saving", this.state.values)
    this.setState({
      validation: {
        name: [{message: 'Falscher Name'}, {message: 'Falscher Hase'}]
      }
    })
    return false
  }
}
