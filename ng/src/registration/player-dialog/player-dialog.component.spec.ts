import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog.component';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { DIALOG_PARAMS } from '../../core/dialog/dialog';

describe('PlayerDialogComponent', () => {
  let component: PlayerDialogComponent;
  let fixture: ComponentFixture<PlayerDialogComponent>;
  let mockActiveModal: jasmine.SpyObj<NgbActiveModal>;
  let mockParams: PlayerDialogParams;

  beforeEach(async () => {
    mockActiveModal = jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']);
    mockParams = {
      // TODO: Use an actual tournament object.
      tournament: {
        id: 'test-tournament',
        name: 'Test Tournament',
        groups: ['A', 'B'],
        config: {
          termsAndConditions: 'Terms and conditions text'
        }
      } as any,
      isManager: false
    };

    await TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: NgbActiveModal, useValue: mockActiveModal },
        { provide: DIALOG_PARAMS, useValue: mockParams }
      ],
      imports: [PlayerDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PlayerDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
