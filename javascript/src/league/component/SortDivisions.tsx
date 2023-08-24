import { Card, Modal } from "react-bootstrap";
import { NsvSaveDialog, NsvSaveDialogState } from "../../core/dialog";
import { Division } from "../types";
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import { LeagueApi } from "../api";

/**
 * Dialog for sorting divisions.
 */
export class SortDivisions extends NsvSaveDialog<NsvSaveDialogState & {
    divisions: Array<Division>
  }> {

  private leagueApi = new LeagueApi()

  constructor(props: any) {
    super(props)
    this.state = {
      title: 'Staffeln umsortieren',
      divisions: [],
    }
  }

  componentDidMount() {
    // TODO: Create proper divisions API
    this.leagueApi.fetchPairings().then(divisions => this.setState({divisions}))
  }

  onDragEnd(droppedItem: any) {
    // Ignore drop outside droppable container
    if (!droppedItem.destination) return;
    var updatedList = [...this.state.divisions];
    // Remove dragged item
    const [reorderedItem] = updatedList.splice(droppedItem.source.index, 1);
    // Add dropped item
    updatedList.splice(droppedItem.destination.index, 0, reorderedItem);
    // Update State
    this.setState({divisions: updatedList});
  }
  
  renderBody() {
    return (
      <Modal.Body>
        <DragDropContext onDragEnd={this.onDragEnd.bind(this)}>
          <Droppable droppableId="division-list">
            {(provided) => (
              <div {...provided.droppableProps} ref={provided.innerRef}>
              {
                this.state.divisions.map((division, index) => (
                  <Draggable key={division.id} draggableId={ '' + division.id } index={ index }>
                    {(provided) => (
                      <Card className="mb-2" ref={provided.innerRef} {...provided.dragHandleProps} {...provided.draggableProps}>
                        <Card.Body>{ division.name }</Card.Body>
                      </Card>
                    )}
                  </Draggable>
                ))
              }
              { provided.placeholder }
              </div>
            )}
          </Droppable>
        </DragDropContext>
      </Modal.Body>
    );
  }

  async save(): Promise<boolean> {
    const ids = this.state.divisions.map(division => division.id)
    await this.leagueApi.updateDivisionSortOrder(ids);
    return true
  }
}
