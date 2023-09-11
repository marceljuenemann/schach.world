import { Form } from "react-bootstrap";
import { NsvForm, NsvFormProps } from "../../core/form";
import { LeagueApi } from "../api";
import React, { ReactNode } from "react";
import { LoadingComponent } from "../../core/loader";
import { TeamVenue } from "../types";
import { NsvDialog } from "../../core/dialog";

class UpdateTeamVenueForm extends React.Component<
  {teamId: number, venue: TeamVenue} & NsvFormProps,
  {values: Record<string, any>}
> {
  private leagueApi = new LeagueApi()

  constructor(props: any) {
    super(props)
    this.state = {
      values: this.props.venue
    }
    this.props.onSave(async () => {
      await this.leagueApi.updateTeamVenue(this.props.teamId, this.state.values as TeamVenue)
    })
  }

  render() {
    return (
      <NsvForm values={this.state.values} onChange={(values) => this.setState({values})} validationErrors={this.props.validationErrors}>
        {(form: NsvForm) => (
          <Form>
            <NsvForm.Control form={form} id="name" label="Name" />
            <NsvForm.Control form={form} id="street" label="Straße und Hausnummer" />
            <NsvForm.Control form={form} id="postCode" label="Postleitzahl" />
            <NsvForm.Control form={form} id="city" label="Stadt" />
            <NsvForm.Control form={form} id="phone" label="Telefon" />
            <NsvForm.Control form={form} id="note" label="Anmerkung" />
          </Form>
        )}
      </NsvForm>
    );
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

export class UpdateTeamVenueDialog extends NsvDialog<{teamId: number}> {
  title = () => 'Spiellokal'
  renderBody(props: NsvFormProps) {
    return <UpdateTeamVenueLoader {...this.props} {...props}></UpdateTeamVenueLoader>
  } 
}
