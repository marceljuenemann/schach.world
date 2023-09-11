import { Card } from "react-bootstrap";
import { NsvDialog } from "../../core/dialog";
import { Division } from "../types";
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import { LeagueApi } from "../api";
import React, { ReactElement, ReactNode } from "react";
import { LoadingComponent } from "../../core/loader";
import { NsvFormProps } from "../../core/form";

class SortDivisions extends React.Component<
  {divisions: Array<Division>} & NsvFormProps,
  {divisions: Array<Division>}
> {
  private leagueApi = new LeagueApi()

  constructor(props: any) {
    super(props)
    this.state = {divisions: Array.from(this.props.divisions)}
    this.props.onSave(async () => {
      const ids = this.state.divisions.map(division => division.id)
      await this.leagueApi.updateDivisionSortOrder(ids)
      return true
    })
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
  
  render() {
    // TODO: Extract into a DraggableList component.
    return (
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
    );
  }
}

class SortDivisionsLoader extends LoadingComponent<NsvFormProps> {
  override async loadComponent(): Promise<ReactNode> {
    const divisions = await new LeagueApi().fetchPairings();
    return <SortDivisions divisions={divisions} {...this.props}></SortDivisions>
  }
}

export class SortDivisionsDialog extends NsvDialog {
  override title(): string {
    return 'Staffeln umsortieren'
  }

  override renderBody(props: NsvFormProps): ReactElement {
    return <SortDivisionsLoader {...props}></SortDivisionsLoader>
  }
}
