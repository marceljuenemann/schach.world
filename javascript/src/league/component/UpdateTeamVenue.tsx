import { Form } from "react-bootstrap";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";
import { ReactNode } from "react";
import { TeamVenue } from "../types";
import { NsvLoadingDialog } from "../../core/dialog";

class UpdateTeamVenueForm extends NsvForm<{teamId: number, venue: TeamVenue}> {
  constructor(props: any) {
    super(props)
    this.state = {values: this.props.venue}
  }

  async save(): Promise<void> {
    await new LeagueApi().updateTeamVenue(this.props.teamId, this.values as TeamVenue)
  }

  render() {
    return (
      <Form>
        <NsvForm.Control form={this} id="name" label="Name" />
        <NsvForm.Control form={this} id="street" label="Straße und Hausnummer" />
        <NsvForm.Control form={this} id="postCode" label="Postleitzahl" />
        <NsvForm.Control form={this} id="city" label="Stadt" />
        <NsvForm.Control form={this} id="phone" label="Telefon" />
        <NsvForm.Control form={this} id="note" label="Anmerkung" />
      </Form>
    )
  }
}

export class UpdateTeamVenueDialog extends NsvLoadingDialog<{venue: TeamVenue}, {teamId: number}> {
  title = () => 'Spiellokal'

  async loadProps() {
    const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {venue: team.venue} 
  }

  renderBodyWithProps(props: {venue: TeamVenue} & NsvFormProps): ReactNode {
    return <UpdateTeamVenueForm {...this.props} {...props} />
  }
}
