import { Form, Modal } from "react-bootstrap";
import { NsvSaveDialog, NsvSaveDialogState } from "../../core/dialog";
import { NsvForm } from "../../core/form";

/**
 * Dialog for sorting divisions.
 */
export class CreateDivision extends NsvSaveDialog<NsvSaveDialogState & {
    values: Record<string, any>
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
        <NsvForm values={this.state.values} onChange={(values) => this.setState({values})} validationErrors={this.state.saveError?.validationErrors}>
          {(form: NsvForm) => (
            <Form>
              <h5 className="mb-3">Staffel</h5>
              <NsvForm.Control form={form} id="name" label="Name" />

              <h5 className="mb-3">Staffelleiter:in</h5>
              <NsvForm.Control form={form} id="managerName" label="Name" />
              <NsvForm.Control form={form} id="managerMail" label="eMail" />
              <NsvForm.Control form={form} id="managerPhone" label="Telefon" />
              <NsvForm.Control form={form} id="managerPhone2" label="Telefon alternativ" />
              <NsvForm.Control form={form} id="managerPassword" label="Passwort" type="password" />
            </Form>
          )}
        </NsvForm>
      </Modal.Body>
    );
  }

  async save(): Promise<void> {
    await this.leagueApi.createDivision(this.state.values)
  }
}
