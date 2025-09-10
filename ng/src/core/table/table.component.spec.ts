import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NsvTableComponent } from './table.component';

describe('NsvTableComponent', () => {
  let component: NsvTableComponent;
  let fixture: ComponentFixture<NsvTableComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NsvTableComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NsvTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
