import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NsvTableComponent, TableOptions, TableColumn } from './table.component';

describe('NsvTableComponent', () => {
  let component: NsvTableComponent;
  let fixture: ComponentFixture<NsvTableComponent>;

  const sampleData = [
    { id: 1, name: 'Charlie', age: 30 },
    { id: 2, name: 'Alice', age: 25 },
    { id: 3, name: 'Bob', age: 35 }
  ];

  const sampleOptions: TableOptions<typeof sampleData[0]> = {
    columns: [
      { id: 'name', label: 'Name', sortable: true },
      { id: 'age', label: 'Age', sortable: true }
    ],
    idFn: (row) => row.id
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NsvTableComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NsvTableComponent);
    component = fixture.componentInstance;

    fixture.componentRef.setInput('options', sampleOptions);
    fixture.componentRef.setInput('data', sampleData);

    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should sort data by name ascending', () => {
    const nameColumn = sampleOptions.columns[0];
    component.onHeaderClick(nameColumn);

    const sortedData = component.sortedData();
    expect(sortedData[0].name).toBe('Alice');
    expect(sortedData[1].name).toBe('Bob');
    expect(sortedData[2].name).toBe('Charlie');
  });

  it('should toggle sort direction', () => {
    const nameColumn = sampleOptions.columns[0];

    // First click - ascending
    component.onHeaderClick(nameColumn);
    expect(component.getSortDirection(nameColumn)).toBe('asc');

    // Second click - descending
    component.onHeaderClick(nameColumn);
    expect(component.getSortDirection(nameColumn)).toBe('desc');

    const sortedData = component.sortedData();
    expect(sortedData[0].name).toBe('Charlie');
    expect(sortedData[2].name).toBe('Alice');
  });

  it('should sort numbers correctly', () => {
    const ageColumn = sampleOptions.columns[1];
    component.onHeaderClick(ageColumn);

    const sortedData = component.sortedData();
    expect(sortedData[0].age).toBe(25);
    expect(sortedData[1].age).toBe(30);
    expect(sortedData[2].age).toBe(35);
  });

  it('should not sort non-sortable columns', () => {
    const nonSortableColumn: TableColumn<typeof sampleData[0], any> = {
      id: 'name',
      label: 'Name',
      sortable: false
    };

    component.onHeaderClick(nonSortableColumn);
    expect(component.getSortDirection(nonSortableColumn)).toBeNull();
  });
});
