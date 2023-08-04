import React from 'react';

class PairingList extends React.Component<{division: any}> {
  constructor(props: any) {
    super(props);
  }

  render() {
    let data = [
      { round: 1, name: "asdewff - wefw", options: "fwef" },
      { round: 2, name: "Kasdasd - wefw", options: "fwef" }
    ]
    return (
      <table className="nsv-table">
        <tr>
          <th>R</th>
          <th>Paarung</th>
          <th>Status</th>
        </tr>
        {
          data.map(row => {
            return <tr>
              <td>{ row.round }.</td>
              <td>{ row.name }</td>
              <td>{ row.options }</td>
            </tr>
          })
        }
      </table>
    );
  }
}
 
export default PairingList;
