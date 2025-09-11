import { Component, OnInit, computed, signal, input } from '@angular/core';

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
  defaultSorting?: SortState
}

@Component({
    selector: 'nsv-table',
    imports: [],
    templateUrl: './table.component.html',
    styleUrl: './table.component.css'
})
export class NsvTableComponent implements OnInit {
  options = input.required<TableOptions<any>>();
  data = input.required<object[]>();

  // TODO: Allow multiple columns.
  sortState = signal<SortState | null>(null);

  sortedData = computed(() => {
    const currentSort = this.sortState();
    const rawData = this.data();

    if (!currentSort || !rawData) {
      return rawData || [];
    }

    return this.sortData(rawData, currentSort);
  });

  ngOnInit() {
    // Set default sorting if provided
    const defaultSorting = this.options()?.defaultSorting;
    if (defaultSorting) {
      this.sortState.set(defaultSorting);
    }
  }

  getValue(row: any, column: TableColumn<any, any>) {
    return column.valueFn ? column.valueFn(row) : row[column.id];
  }

  onHeaderClick(column: TableColumn<any, any>) {
    if (column.sortable === false) {
      return;
    }

    const currentSort = this.sortState();
    let newSort: SortState;

    if (currentSort?.columnId === column.id) {
      // Toggle direction for same column
      newSort = {
        columnId: column.id,
        direction: currentSort.direction === 'asc' ? 'desc' : 'asc'
      };
    } else {
      // New column, start with ascending
      newSort = {
        columnId: column.id,
        direction: 'asc'
      };
    }

    this.sortState.set(newSort);
  }

  getSortDirection(column: TableColumn<any, any>): 'asc' | 'desc' | null {
    const currentSort = this.sortState();
    return currentSort?.columnId === column.id ? currentSort.direction : null;
  }

  isSorted(column: TableColumn<any, any>): boolean {
    return this.sortState()?.columnId === column.id;
  }

  private sortData(data: any[], sortState: SortState): any[] {
    const { columnId, direction } = sortState;
    const column = this.options().columns.find((c: TableColumn<any, any>) => c.id === columnId);

    if (!column) {
      return data;
    }

    return [...data].sort((a, b) => {
      const valueA = this.getValue(a, column);
      const valueB = this.getValue(b, column);

      let comparison = 0;

      // Handle null/undefined values
      if (valueA == null && valueB == null) return 0;
      if (valueA == null) return 1;
      if (valueB == null) return -1;

      // Compare values
      if (typeof valueA === 'string' && typeof valueB === 'string') {
        comparison = valueA.localeCompare(valueB);
      } else if (typeof valueA === 'number' && typeof valueB === 'number') {
        comparison = valueA - valueB;
      } else {
        // Convert to string for comparison
        comparison = String(valueA).localeCompare(String(valueB));
      }

      return direction === 'asc' ? comparison : -comparison;
    });
  }

}
