import { CommonModule } from '@angular/common';
import { Component, TemplateRef, computed, input, linkedSignal, Signal } from '@angular/core';
import { NgbDropdownModule } from '@ng-bootstrap/ng-bootstrap';

export type TableColumn<Row extends object, Value> = {
  id: string,
  label: string,
  valueFn?: (row: Row) => Value,  // Defaults to row[column.id]
  sortable?: boolean  // Defaults to true
  defaultSortDirection?: 'asc' | 'desc'  // Defaults to 'asc'
  templateRef?: Signal<TemplateRef<any>>  // Default to displaying the value
}

export type SortState = {
  columnId: string,
  direction: 'asc' | 'desc'
}

export type TableOptions<Row extends object> = {
  columns: TableColumn<Row, any>[]
  idFn: (row: Row) => string | number
  defaultSorting?: SortState[]
  showColumnSelection?: boolean
}

@Component({
    selector: 'nsv-table',
    imports: [NgbDropdownModule, CommonModule],
    templateUrl: './table.component.html',
    styleUrl: './table.component.css'
})
export class NsvTableComponent {
  options = input.required<TableOptions<any>>();
  data = input.required<object[]>();

  // We use an array of SortState to allow multi-column sorting.
  sortState = linkedSignal<SortState[]>(() => {
    return this.options().defaultSorting || [];
  });
  sortColumn = computed(() => {
    return this.sortState().length ? this.sortState()[0].columnId : null;
  });
  sortDirection = computed(() => {
    return this.sortState().length ? this.sortState()[0].direction : null;
  });
  sortedData = computed(() => {
    if (!this.sortState().length) {
      return this.data();
    } else {
      return [...this.data()].sort((a, b) => {
        for (const sort of this.sortState()) {
          const aValue = this.getValue(a, this.getColumn(sort.columnId));
          const bValue = this.getValue(b, this.getColumn(sort.columnId));
          if (aValue < bValue) return sort.direction === 'asc' ? -1 : 1;
          if (aValue > bValue) return sort.direction === 'asc' ? 1 : -1;
        }
        return 0;
      });
    }
  });

  onHeaderClick(column: TableColumn<any, any>) {
    if (column.sortable === false) return;
    this.sortState.update((sortState: SortState[]) => {
      let direction = column.defaultSortDirection || 'asc';
      if (this.sortColumn() === column.id) {
        direction = this.sortDirection() === 'asc' ? 'desc' : 'asc';
      }
      return [{ columnId: column.id, direction } as SortState]
        .concat(sortState.filter(s => s.columnId !== column.id));
    })
  }

  getColumn(columnId: string): TableColumn<any, any> {
    const column = this.options().columns.find(col => col.id === columnId);
    if (!column) {
      throw new Error(`Column with id ${columnId} not found`);
    }
    return column;
  }

  getValue(row: any, column: TableColumn<any, any>) {
    return column.valueFn ? column.valueFn(row) : row[column.id];
  }
}
