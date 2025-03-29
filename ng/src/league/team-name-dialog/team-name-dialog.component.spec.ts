import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TeamNameDialogComponent } from './team-name-dialog.component';

describe('TeamNameDialogComponent', () => {
  let component: TeamNameDialogComponent;
  let fixture: ComponentFixture<TeamNameDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TeamNameDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TeamNameDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
