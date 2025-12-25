import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ConfirmationDialogComponent, ConfirmationDialogParams } from './confirmation-dialog.component';
import { DIALOG_PARAMS } from '../dialog';

describe('ConfirmationDialogComponent', () => {
  let component: ConfirmationDialogComponent<void>;
  let fixture: ComponentFixture<ConfirmationDialogComponent<void>>;
  let mockActiveModal: jasmine.SpyObj<NgbActiveModal>;
  let mockParams: ConfirmationDialogParams<void>;

  beforeEach(async () => {
    mockActiveModal = jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']);
    mockParams = {
      title: 'Test Title',
      message: 'Test Message',
      confirmText: 'Confirm',
      onConfirm: jasmine.createSpy('onConfirm').and.returnValue(Promise.resolve())
    };

    await TestBed.configureTestingModule({
      imports: [ConfirmationDialogComponent],
      providers: [
        { provide: NgbActiveModal, useValue: mockActiveModal },
        { provide: DIALOG_PARAMS, useValue: mockParams }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ConfirmationDialogComponent<void>);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
