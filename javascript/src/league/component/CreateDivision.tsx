import { Form } from "react-bootstrap";
import { NsvDialog } from "../../core/dialog";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";

class CreateDivisionForm extends NsvForm {

  async save(): Promise<void> {
    await new LeagueApi().createDivision(this.values)
  }

  render() {
    return (
      <Form>
        <h5 className="mb-3">Staffel</h5>
        <NsvForm.Control form={this} id="name" label="Name" />

        <h5 className="mb-3">Staffelleiter:in</h5>
        <NsvForm.Control form={this} id="managerName" label="Name" />
        <NsvForm.Control form={this} id="managerMail" label="eMail" />
        <NsvForm.Control form={this} id="managerPhone" label="Telefon" />
        <NsvForm.Control form={this} id="managerPhone2" label="Telefon alternativ" />
        <NsvForm.Control form={this} id="managerPassword" label="Passwort" type="password" />
      </Form>
    )
  }
}

export class CreateDivisionDialog extends NsvDialog {
  title = () => 'Neue Staffel'
  renderBody = (props: NsvFormProps) => <CreateDivisionForm {...props}></CreateDivisionForm>
}
