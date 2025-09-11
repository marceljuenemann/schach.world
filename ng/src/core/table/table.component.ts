import { Component, OnInit, computed, signal, input, linkedSignal } from '@angular/core';

export type TableColumn<Row extends object, Value> = {
  id: string,
  label: string,
  valueFn?: (row: Row) => Value,
  sortable?: boolean  // Default to true
}

export type SortState = {
  columnId: string,
  direction: 'asc' | 'desc'
}

export type TableOptions<Row extends object> = {
  columns: TableColumn<Row, any>[]
  idFn: (row: Row) => string | number
  defaultSorting?: SortState[]
}

@Component({
    selector: 'nsv-table',
    imports: [],
    templateUrl: './table.component.html',
    styleUrl: './table.component.css'
})
export class NsvTableComponent {
  options = input.required<TableOptions<any>>();
  data = input.required<object[]>();

  sortState = linkedSignal<SortState[]>(() => this.options().defaultSorting || []);
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

  onHeaderClick(column: TableColumn<any, any>) {
    if (column.sortable === false) {
      return;
    }
  }
}
