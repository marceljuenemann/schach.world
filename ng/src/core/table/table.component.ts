import { Component, Input } from '@angular/core';

export type TableColumn<Row extends object> = {
  id: string,
  label: string,
  valueFn?: (row: Row) => any
}

export type TableOptions<Row extends object> = {
  columns: TableColumn<Row>[]
  idFn: (row: Row) => string | number
}

@Component({
  selector: 'nsv-table',
  standalone: true,
  imports: [],
  templateUrl: './table.component.html',
  styleUrl: './table.component.css'
})
export class NsvTableComponent {

  @Input({required: true}) options: TableOptions<any>
  @Input({required: true}) data: object[]

  getValue(row: any, column: TableColumn<any>) {
    return column.valueFn ? column.valueFn(row) : row[column.id];
  }

}
