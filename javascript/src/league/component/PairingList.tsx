import React, { useEffect } from 'react';

class PairingList extends React.Component<{division: any}, {pairings: any}> {
  state = {
    pairings: []
  }

  // TODO: move to abstact NSV component.
  componentDidMount() {
    // TODO: store in flight calls.
    console.log(this.props.division)
    this.fetchPairings().then(pairings => this.setState({pairings}))
  }

  private async fetchPairings() {
    const response = await fetch("https://localhost/ligen/test-2022/api/pairings/", { mode: 'no-cors' })
    return await response.json()
  }

  render() {
    return (
      <table className="nsv-table">
        <thead>
          <tr>
            <th>R</th>
            <th>Paarung</th>
            <th>Status {this.props.division}</th>
          </tr>
        </thead>
        <tbody> {
          this.state.pairings.map((pairing: any) => {
            return <tr key={ pairing.id }>
              <td>{ pairing.round }.</td>
              <td>{ pairing.team1.name } - { pairing.team2.name }</td>
              <td></td>
            </tr>
          })
        } </tbody>
      </table>
    );
  }
}
 
export default PairingList;
