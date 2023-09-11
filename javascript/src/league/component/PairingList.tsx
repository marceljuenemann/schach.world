import React, { ReactNode } from 'react';
import { LeagueApi } from '../api';
import { Division } from '../types';
import { Alert, Col, Form, Row } from 'react-bootstrap';
import { LoadingComponent } from '../../core/loader';

const CURRENT_ROUND = -1

/**
 * Displays a list of all pairings that the user can edit.
 */
// TODO: Add tests.
export class PairingList extends React.Component<{
    // The ID of the division the user is allowed to edit, or 0 if they may edit all divisions.
    division: number
    divisions: Array<Division>,
  }, {
    selectedDivision: number,
    selectedRound: number
  }> {

  constructor(props: any) {
    super(props)
    this.state = {
      selectedDivision: 0,
      selectedRound: CURRENT_ROUND
    }
  }

  rounds(): Set<number> {
    let rounds = new Set<number>();
    for (let division of this.props.divisions) {
      for (let matchDay of division.matchDays) {
        rounds.add(matchDay.round);
      }
    }
    return rounds;
  }

  hasPairings(): boolean {
    for (let division of this.props.divisions) {
      for (let matchDay of division.matchDays) {
        if (matchDay.pairings.length) return true
      }
    }
    return false;
  }

  *pairings() {
    for (let division of this.props.divisions) {
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
    if (!this.hasPairings()) {
      return <Alert variant='info'>Noch keine Paarungen hinterlegt</Alert>
    }
    return (
      <div>
        <Form className="d-inline-block mb-2">
          <Row>
            {/* Division selector (only for league managers) */}
            { !this.props.division &&
              <Col xs="auto">
                <Form.Select value={ this.state.selectedDivision } 
                    onChange={ e => this.setState({selectedDivision: parseInt(e.target.value)}) }
                    size="sm"
                    aria-label="Staffelauswahl">
                  <option value="0">Alle Staffeln</option>
                  {
                    Array.from(this.props.divisions).map(division => (
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
              { !this.props.division && <th>Staffel</th> }
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
                { !this.props.division && <td>{ pairing.division.name }</td> }
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

export class PairingListLoader extends LoadingComponent<{division: number}> {
  private leagueApi = new LeagueApi()

  async loadComponent(): Promise<ReactNode> {
    let divisions: Array<Division> = await this.leagueApi.fetchPairings()
    if (this.props.division) {
      divisions = divisions.filter(d => d.id === this.props.division)
    }
    return <PairingList division={this.props.division} divisions={divisions}></PairingList>
  }
}
