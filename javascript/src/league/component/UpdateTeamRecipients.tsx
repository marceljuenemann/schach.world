import { NsvFormProps } from "../../core/form";
import React, { FormEvent, ReactNode } from "react";
import { NsvLoadingDialog } from "../../core/dialog";
import { Alert, Button, Card, Form, InputGroup } from "react-bootstrap";
import { LeagueApi } from "../api";

/**
 * Shows a list of email addresses, allowing to add and delete them. 
 */
class UpdateTeamRecipientsForm extends React.Component<{teamId: number, emails: Array<String>} & NsvFormProps, {emails: Array<string>, newEmail: string}> {

  constructor(props: {emails: Array<string>, teamId: number} & NsvFormProps) {
    super(props)
    this.state = {emails: props.emails, newEmail: ''}
    this.props.onSave(() => this.save())
  }

  private async save(): Promise<void> {
    await new LeagueApi().updateTeamRecipients(this.props.teamId, this.state.emails)
  }

  private onSubmit(e: FormEvent) {
    this.setState({
      emails: [...this.state.emails, this.state.newEmail],
      newEmail: ''
    })
    e.preventDefault()
  }

  private remove(index: number) {
    const list = [...this.state.emails]
    list.splice(index, 1)
    this.setState({emails: list})
  }

  render() {
    return <Form onSubmit={this.onSubmit.bind(this)}>
      <InputGroup>
        <InputGroup.Text>
          <span className="dashicons dashicons-plus"></span>
        </InputGroup.Text>
        <Form.Control
          placeholder="E-Mail Empfänger hinzufügen"
          type="email"
          required={true}
          value={this.state.newEmail}
          onChange={e => this.setState({newEmail: e.target.value})}
        />
        <Button type="submit" variant="primary" id="button-addon2">
          Hinzufügen
        </Button>        
      </InputGroup>
      {
        this.state.emails.map((email, index) => {
          return <Card className="mt-2" key={email}>
            <Card.Body className="d-flex justify-content-between flex-wrap">
              <span>{ email }</span>
              <span onClick={() => this.remove(index)} className="dashicons dashicons-trash" style={{cursor: "pointer"}}></span>
              {
                this.props.validationErrors && this.props.validationErrors[`recipients[${index}]`] && this.props.validationErrors[`recipients[${index}]`].map((error, i) => {
                  return <Alert variant='danger' key={i} className="w-100 mt-2">{ error.message }</Alert>
                })
              }
            </Card.Body>
          </Card>
        })
      }
     </Form>
  }
}

export class UpdateTeamRecipientsDialog extends NsvLoadingDialog<{emails: Array<String>}, {teamId: number}> {
  title = () => 'Zusätzliche E-Mail Empfänger'

  async loadProps() {
    //const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {emails: ['hello@example.com']}
  }

  renderBodyWithProps(props: {emails: Array<string>} & NsvFormProps): ReactNode {
    return <UpdateTeamRecipientsForm {...props} {...this.props} />
  }
}
