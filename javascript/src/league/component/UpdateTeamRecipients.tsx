import { NsvFormProps } from "../../core/form";
import React, { FormEvent, ReactNode } from "react";
import { NsvLoadingDialog } from "../../core/dialog";
import { Button, Card, Form, InputGroup } from "react-bootstrap";

/**
 * Shows a list of email addresses, allowing to add and delete them. 
 */
class EmailList extends React.Component<{emails: Array<String>}, {emails: Array<string>, newEmail: string}> {

  constructor(props: {emails: Array<string>}) {
    super(props)
    this.state = {emails: props.emails, newEmail: ''}
  }

  onSubmit(e: FormEvent) {
    this.setState({
      emails: [...this.state.emails, this.state.newEmail],
      newEmail: ''
    })
    e.preventDefault()
  }

  remove(index: number) {
    const list = this.state.emails
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
            <Card.Body className="d-flex justify-content-between">
              <span>{ email }</span>
              <span onClick={() => this.remove(index)} className="dashicons dashicons-trash" style={{cursor: "pointer"}}></span>
            </Card.Body>
          </Card>
        })
      }
     </Form>
  }
}

export class UpdateTeamRecipientsDialog extends NsvLoadingDialog<{recipients: Array<String>}, {teamId: number}> {
  title = () => 'Zusätzliche E-Mail Empfänger'

  async loadProps() {
    //const team = await new LeagueApi().fetchTeam(this.props.teamId)
    return {recipients: ['hello@example.com']}
  }

  renderBodyWithProps(props: {recipients: Array<string>} & NsvFormProps): ReactNode {
    return <EmailList emails={props.recipients} />
  }
}
