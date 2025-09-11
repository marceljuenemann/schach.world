import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NsvTableComponent, TableOptions, TableColumn } from './table.component';

type SampleRow = { id: number, name: string, age: number };

describe('NsvTableComponent', () => {
  let component: NsvTableComponent;
  let fixture: ComponentFixture<NsvTableComponent>;

  const sampleData: SampleRow[] = [
    { id: 1, name: 'Charlie', age: 30 },
    { id: 2, name: 'Alice', age: 25 },
    { id: 3, name: 'Bob', age: 35 }
  ];

  const sampleOptions: TableOptions<SampleRow> = {
    columns: [
      { id: 'name', label: 'Name', sortable: true },
      { id: 'age', label: 'Age', sortable: true, defaultSortDirection: 'desc' }
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

  it('should sort by name ascending', () => {
    component.onHeaderClick(sampleOptions.columns[0]);

    const sortedData = component.sortedData() as SampleRow[];
    expect(sortedData[0].name).toBe('Alice');
    expect(sortedData[1].name).toBe('Bob');
    expect(sortedData[2].name).toBe('Charlie');
    expect(component.sortColumn()).toBe('name');
    expect(component.sortDirection()).toBe('asc');
  });

  it('should sort by age descending', () => {
    component.onHeaderClick(sampleOptions.columns[1]);

    const sortedData = component.sortedData() as SampleRow[];
    expect(sortedData[0].name).toBe('Bob');
    expect(sortedData[1].name).toBe('Charlie');
    expect(sortedData[2].name).toBe('Alice');
    expect(component.sortColumn()).toBe('age');
    expect(component.sortDirection()).toBe('desc');
  });

  it('should toggle sort direction', () => {
    component.onHeaderClick(sampleOptions.columns[0]);
    component.onHeaderClick(sampleOptions.columns[0]);

    const sortedData = component.sortedData() as SampleRow[];
    expect(sortedData[0].name).toBe('Charlie');
    expect(sortedData[1].name).toBe('Bob');
    expect(sortedData[2].name).toBe('Alice');
    expect(component.sortColumn()).toBe('name');
    expect(component.sortDirection()).toBe('desc');
  });

  it('should not sort non-sortable columns', () => {
    const nonSortableColumn: TableColumn<typeof sampleData[0], any> = {
      id: 'name',
      label: 'Name',
      sortable: false
    };

    component.onHeaderClick(nonSortableColumn);
    expect(component.sortColumn()).toBeNull
  });
});
