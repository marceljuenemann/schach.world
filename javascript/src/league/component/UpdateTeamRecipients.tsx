import { NsvFormProps } from "../../core/form";
import React, { ReactNode } from "react";
import { NsvLoadingDialog } from "../../core/dialog";

/**
 * Shows a list of email addresses, allowing to add and delete them. 
 */
class EmailList extends React.Component<{emails: Array<String>}, {emails: Array<string>}> {


}

export class UpdateTeamRecipientsDialog extends NsvLoadingDialog<{recipients: Array<String>}, {teamId: number}> {
  title = () => 'Zusätzliche E-Mail Empfänger'

  async loadProps() {
    //const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {recipients: ['hello@example.com']}
  }

  renderBodyWithProps(props: {recipients: Array<string>} & NsvFormProps): ReactNode {
    return <div {...this.props} {...props}>Hello World</div>
  }
}
