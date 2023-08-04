import React from 'react';
import { fetchPairings } from '../api';
import { Pairing } from '../types';
import { Context } from '../../context';
import { Col, FloatingLabel, Form, Row } from 'react-bootstrap';

// TODO: move to abstact NSV component.
class PairingList extends React.Component<{context: Context}, {pairings: Array<Pairing>, round: number}> {

  constructor(props: any) {
    super(props)
    this.state = {
      pairings: [],
      round: 2
    }
  }

  rounds(): Set<number> {
    return new Set(this.state.pairings.map(p => p.round))
  }

  get division() {
    return this.props.context.attribute('division')
  }

  componentDidMount() {
    // TODO: store in flight calls and cancel them when needed.
    fetchPairings().then(pairings => this.setState({pairings}))
  }

  render() {
    return (
      <div>
        <Form className="d-inline-block mb-2">
          <Form.Select value={ this.state.round } 
              onChange={ e => this.setState({round: parseInt(e.target.value)}) }
              size="sm"
              aria-label="Runden auswahl">
            <option value="0">Alle Runden</option>
            {
              Array.from(this.rounds()).map(round => <option key={ round } value={ round }>
                Runde { round }
              </option>)
            }
          </Form.Select>
        </Form>
        <table className="nsv-table">
          <thead>
            <tr>
              <th>R</th>
              <th>Paarung</th>
              <th>Eingeben</th>
              <th>Optionen</th>
            </tr>
          </thead>
          <tbody>{
            this.state.pairings.filter(pairing => {
              return !this.state.round || this.state.round == pairing.round 
            }).map(pairing => {
              const uri = `?admin=alleeing---&pid=${pairing.id}`
              return <tr key={ pairing.id } className='text-nowrap'>
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
 
export default PairingList;
