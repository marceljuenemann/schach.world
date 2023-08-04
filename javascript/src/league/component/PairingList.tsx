import React from 'react';

class PairingList extends React.Component<{division: any}> {
  constructor(props: any) {
    super(props);
  }

  render() {
    return (
      <div>
        Hello NSV World! Division: {this.props.division}
      </div>
    );
  }
}
 
export default PairingList;
