import React from 'react';
import { fetchPairings } from '../api';
import { Division, Pairing } from '../types';
import { Context } from '../../context';
import { Col, Form, Row } from 'react-bootstrap';

const CURRENT_ROUND = -1

/**
 * Displays a list of all pairings that the user can edit.
 */
// TODO: move to abstact NSV component.
export class PairingList extends React.Component<{context: Context}, {
    divisions: Array<Division>,
    selectedDivision: number,
    selectedRound: number
  }> {

  constructor(props: any) {
    super(props)
    this.state = {
      divisions: [],
      selectedDivision: 0,
      selectedRound: CURRENT_ROUND
    }
  }

  componentDidMount() {
    // TODO: store in flight calls and cancel them when needed.
    fetchPairings().then(divisions => this.setState({divisions}))
  }

  /**
   * Returns the ID of the division that the user is allowed to edit,
   * or 0 if they are allowed to edit all divisions.
   */
  get division() {
    return parseInt(this.props.context.attribute('division') || '0')
  }

  rounds(): Set<number> {
    let rounds = new Set<number>();
    for (let division of this.state.divisions) {
      if (this.division && division.id != this.division) continue;
      for (let matchDay of division.matchDays) {
        rounds.add(matchDay.round);
      }
    }
    return rounds;
  }

  *pairings() {
    for (let division of this.state.divisions) {
      if (this.division && division.id != this.division) continue;
      if (this.state.selectedDivision && division.id != this.state.selectedDivision) continue;
      for (let matchDay of division.matchDays) {
        if (this.state.selectedRound > 0 && matchDay.round != this.state.selectedRound) continue;
        if (this.state.selectedRound == CURRENT_ROUND && matchDay.date != division.closestDate) continue;
        for (let pairing of matchDay.pairings) {
          yield {...pairing, division};
        }
      }
    }
  }

  render() {
    return (
      <div>
        <Form className="d-inline-block mb-2">
          <Row>
            {/* Division selector (only for league managers) */}
            { !this.division &&
              <Col xs="auto">
                <Form.Select value={ this.state.selectedDivision } 
                    onChange={ e => this.setState({selectedDivision: parseInt(e.target.value)}) }
                    size="sm"
                    aria-label="Staffelauswahl">
                  <option value="0">Alle Staffeln</option>
                  {
                    Array.from(this.state.divisions).map(division => (
                      <option key={ division.id } value={ division.id }>{ division.name }</option>
                    ))
                  }
                </Form.Select>
              </Col>
            }

            {/* Round selector */}
            <Col xs="auto">
              <Form.Select value={ this.state.selectedRound } 
                  onChange={ e => this.setState({selectedRound: parseInt(e.target.value)}) }
                  size="sm"
                  aria-label="Rundenauswahl">
                <option value="-1">Aktuelle Runde</option>
                <option value="0">Alle Runden</option>
                {
                  Array.from(this.rounds()).map(round => (
                    <option key={ round } value={ round }>Runde { round }</option>
                  ))
                }
              </Form.Select>
            </Col>
          </Row>
        </Form>
        <table className="nsv-table">
          <thead>
            <tr>
              { !this.division && <th>Staffel</th> }
              <th>R</th>
              <th>Paarung</th>
              <th>Eingeben</th>
              <th>Optionen</th>
            </tr>
          </thead>
          <tbody>{
            Array.from(this.pairings()).map(pairing => {
              const uri = `?admin=alleeing---&pid=${pairing.id}`
              return <tr key={ pairing.id } className='text-nowrap'>
                { !this.division && <td>{ pairing.division.name }</td> }
                <td>{ pairing.round }</td>
                <td>
                  <a href={ uri }>
                    { pairing.team1.name }
                    &nbsp;&ndash;&nbsp;
                    { pairing.team2.name }
                  </a>
                </td>
                <td>
                  <a href={ uri }><img src='/ligen/_templates/systemicons/desk_eingeben.png' alt='Eingeben' className='sed_admin_icon' />{ pairing.result ? 'Bearbeiten' : 'Eingeben' }</a>
                </td>
                <td>
                  <a href={ uri + '#zusatz' }><img src='/ligen/_templates/systemicons/desk_einstellungen.png' alt='Einstellungen' className='sed_admin_icon' />Optionen&nbsp;</a>
                </td>
              </tr>
            })
          }</tbody>
        </table>
      </div>
    );
  }
}
