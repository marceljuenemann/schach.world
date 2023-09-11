import { Form, Modal } from "react-bootstrap";
import { NsvDialog } from "../../core/dialog";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";
import React, { ReactNode } from "react";

class CreateDivisionForm extends React.Component<
  NsvFormProps,
  {values: Record<string, any>}
> {
  private leagueApi = new LeagueApi()

  constructor(props: any) {
    super(props)
    this.state = {
      values: {}
    }
    this.props.onSave(async () => {
      await this.leagueApi.createDivision(this.state.values)
      return true
    })
  }

  render() {
    return (
      <NsvForm values={this.state.values} onChange={(values) => this.setState({values})} validationErrors={this.props.validationErrors}>
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
    );
  }
}

export class CreateDivisionDialog extends NsvDialog {
  override title(): string {
    return 'Neue Staffel'
  }

  override renderBody(props: NsvFormProps): ReactNode {
    return <CreateDivisionForm {...props}></CreateDivisionForm>
  } 
}
