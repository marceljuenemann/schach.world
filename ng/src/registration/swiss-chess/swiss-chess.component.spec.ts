import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SwissChessComponent } from './swiss-chess.component';

describe('SwissChessComponent', () => {
  let component: SwissChessComponent;
  let fixture: ComponentFixture<SwissChessComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SwissChessComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SwissChessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
