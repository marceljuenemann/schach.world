import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideHttpClientTesting } from '@angular/common/http/testing';

import { TeamNameDialog } from './team-name-dialog.component';
import { provideHttpClient } from '@angular/common/http';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { TeamNameDialogParams } from './team-name-dialog.component';
import { DIALOG_PARAMS } from '../../core/dialog/dialog';

describe('TeamNameDialogComponent', () => {
  let component: TeamNameDialog;
  let fixture: ComponentFixture<TeamNameDialog>;
  let mockActiveModal: jasmine.SpyObj<NgbActiveModal>;
  let mockParams: TeamNameDialogParams;

  beforeEach(async () => {
    mockActiveModal = jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']);
    mockParams = {
      id: 32,
      name: 'Test Team',
      number: 2
    }

    await TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: NgbActiveModal, useValue: mockActiveModal },
        { provide: DIALOG_PARAMS, useValue: mockParams }
      ],
      imports: [TeamNameDialog],
    })
    .compileComponents();

    fixture = TestBed.createComponent(TeamNameDialog);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
