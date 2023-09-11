import { Form } from "react-bootstrap";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";
import React, { ReactNode } from "react";
import { LoadingComponent } from "../../core/loader";
import { TeamVenue } from "../types";
import { NsvDialog } from "../../core/dialog";

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

class UpdateTeamVenueLoader extends LoadingComponent<{venue: TeamVenue}, {teamId: number} & NsvFormProps> {
  async loadProps() {
    const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {venue: team.venue} 
  }

  renderWithProps(props: {venue: TeamVenue}): ReactNode {
    return <UpdateTeamVenueForm {...this.props} {...props}></UpdateTeamVenueForm>
  }
}

// TODO: NsvLoadingDialog for less boilerplate
export class UpdateTeamVenueDialog extends NsvDialog<{teamId: number}> {
  title = () => 'Spiellokal'
  renderBody(props: NsvFormProps) {
    return <UpdateTeamVenueLoader {...this.props} {...props}></UpdateTeamVenueLoader>
  } 
}
