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
      // TODO: New API call.
      await this.leagueApi.createDivision(this.state.values)
    })
  }

  render() {
    return (
      <NsvForm values={this.state.values} onChange={(values) => this.setState({values})} validationErrors={this.props.validationErrors}>
        {(form: NsvForm) => (
          <Form>
            <NsvForm.Control form={form} id="name" label="Name" />
            <NsvForm.Control form={form} id="note" label="Anmerkung" />
          </Form>
        )}
      </NsvForm>
    );
  }
}

class UpdateTeamVenueLoader extends LoadingComponent<NsvFormProps> {
  override async loadComponent(): Promise<ReactNode> {
    const team = await new LeagueApi().fetchTeam(3) // TODO: ID
    return <UpdateTeamVenueForm teamId={team.id} venue={team.venue} {...this.props}></UpdateTeamVenueForm>
  }
}

export class UpdateTeamVenueDialog extends NsvDialog {
  override title(): string {
    return 'Spiellokal'
  }

  renderBody(props: NsvFormProps): ReactNode {
    return <UpdateTeamVenueLoader {...props}></UpdateTeamVenueLoader>
  } 
}
