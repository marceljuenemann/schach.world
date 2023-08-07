import React from 'react';
import { LeagueApi } from '../api';
import { Division } from '../types';
import { Context } from '../../context';
import { Button, Col, Form, Modal, Row } from 'react-bootstrap';
import { DialogContext } from '../../dialog';

/**
 * Dialog for sorting divisions.
 */
// TODO: move to abstact NSV component.
export class SortDivisions extends React.Component<{context: DialogContext}, {
    divisions: Array<Division>,
  }> {

  private api: LeagueApi

  constructor(props: any) {
    super(props)
    this.api = new LeagueApi(this.props.context.context);
    this.state = {
      divisions: [],
    }
  }

  componentDidMount() {
    //this.api.fetchPairings().then(divisions => this.setState({divisions}))
  }

  render() {
    return (
      <Modal
        show={true}
        aria-labelledby="modal-title"
        centered
      >
        <Modal.Header closeButton>
          <Modal.Title id="modal-title">
            Staffeln umsortieren
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <h4>Centered Modal</h4>
          <p>
            Cras mattis consectetur purus sit amet fermentum. Cras justo odio,
            dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac
            consectetur ac, vestibulum at eros.
          </p>
        </Modal.Body>
        <Modal.Footer>
          <Button onClick={this.props.context.onClose}>Close</Button>
        </Modal.Footer>
      </Modal>
    );
  }
}
