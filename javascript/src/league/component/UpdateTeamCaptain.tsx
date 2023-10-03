import { Form } from "react-bootstrap";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";
import { ReactNode } from "react";
import { TeamCaptain } from "../types";
import { NsvLoadingDialog } from "../../core/dialog";

class UpdateTeamCaptainForm extends NsvForm<{teamId: number, captain: TeamCaptain}> {
  constructor(props: any) {
    super(props)
    this.state = {values: this.props.captain}
  }

  async save(): Promise<void> {
    await new LeagueApi().updateTeamCaptain(this.props.teamId, this.values as TeamCaptain)
  }

  render() {
    return (
      <Form>
        <NsvForm.Control form={this} id="name" label="Name" />
        <NsvForm.Control form={this} id="mail" label="eMail" />
        <NsvForm.Control form={this} id="phone" label="Telefon" />
        <NsvForm.Control form={this} id="phone2" label="Telefon (alternativ)" />
      </Form>
    )
  }
}

export class UpdateTeamCaptainDialog extends NsvLoadingDialog<{captain: TeamCaptain}, {teamId: number}> {
  title = () => 'Mannschaftsführer:in'

  async loadProps() {
    const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {captain: team.captain} 
  }

  renderBodyWithProps(props: {captain: TeamCaptain} & NsvFormProps): ReactNode {
    return <UpdateTeamCaptainForm {...this.props} {...props} />
  }
}
