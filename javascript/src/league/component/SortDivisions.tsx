import { Modal } from "react-bootstrap";
import { NsvDialog, NsvSaveDialog } from "../../core/dialog";
import { Division } from "../types";

/**
 * Dialog for sorting divisions.
 */
export class SortDivisions extends NsvSaveDialog<{
    divisions: Array<Division>
  }> {

  constructor(props: any) {
    super(props)
    this.state = {
      title: 'Staffeln umsortieren',
      divisions: [],
    }
  }

  componentDidMount() {
    //this.api.fetchPairings().then(divisions => this.setState({divisions}))
  }

  renderBody() {
    return (
      <Modal.Body>
        <p>
          Cras mattis consectetur purus sit amet fermentum. Cras justo odio,
          dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac
          consectetur ac, vestibulum at eros.
        </p>
      </Modal.Body>
    );
  }

  save(): boolean {
    return true
  }
}
