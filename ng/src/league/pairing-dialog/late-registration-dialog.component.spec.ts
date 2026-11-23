import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { of } from 'rxjs';

import { DIALOG_PARAMS } from '../../core/dialog/dialog';
import { DwzPlayer, DwzService } from '../../dwz/dwz.service';
import { PlayerSelectComponent } from '../shared/player-select/player-select.component';
import { LateRegistrationDialog, LateRegistrationDialogParams } from './late-registration-dialog.component';

const TEST_DWZ_PLAYER = {
  name: 'Jünemann, Marcel',
  club: 'SK Lehrte',
  zps: '70156',
  memberId: '0116',
  gender: 'M',
  yearOfBirth: 2000,
  dwz: 1600,
  elo: 1750,
  fideTitle: 'FM',
} as DwzPlayer

describe('LateRegistrationDialog', () => {
  let dialog: LateRegistrationDialog;
  let fixture: ComponentFixture<LateRegistrationDialog>;

  const createDialog = async (overrides: Partial<LateRegistrationDialogParams> = {}) => {
    const params: LateRegistrationDialogParams = {teamName: 'SK Lehrte 2', teamNumbers: [2, 3], preferredZps: '70156', ...overrides};
    const dwzService = jasmine.createSpyObj('DwzService', ['findPlayer']);
    dwzService.findPlayer.and.returnValue(of([]));

    await TestBed.configureTestingModule({
      providers: [
        { provide: DwzService, useValue: dwzService },
        { provide: NgbActiveModal, useValue: jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']) },
        { provide: DIALOG_PARAMS, useValue: params },
      ],
      imports: [LateRegistrationDialog],
    })
    .compileComponents();

    fixture = TestBed.createComponent(LateRegistrationDialog);
    dialog = fixture.componentInstance;
    fixture.detectChanges();
  };

  const selectDatabaseEntry = () => dialog.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
  const selectManualEntry = () => dialog.selectedPlayer.setValue({name: 'Neumann, Nora'});
  const fieldCount = () => {
    fixture.detectChanges();
    return fixture.nativeElement.querySelectorAll('nsv-form input').length;
  };

  it('should use the player select with the preferred ZPS', async () => {
    await createDialog();
    const playerSelect = fixture.debugElement.query(By.directive(PlayerSelectComponent)).componentInstance;
    expect(playerSelect.control).toBe(dialog.selectedPlayer);
    expect(playerSelect.preferredZps).toBe('70156');
  });

  it('should offer all teams and preselect the first one', async () => {
    await createDialog();
    const select = fixture.nativeElement.querySelector('select');
    expect(Array.from<HTMLOptionElement>(select.options).map(option => option.text)).toEqual(['2. Mannschaft', '3. Mannschaft']);
    expect(dialog.teamForm.controls.teamNumber.value).toBe('2');
  });

  it('should not ask for the team if there is only one', async () => {
    await createDialog({teamNumbers: [2]});
    expect(fixture.nativeElement.querySelector('select')).toBeNull();
    selectManualEntry();
    expect((await dialog.save()).teamNumber).toBe(2);
  });

  it('should return the selected team', async () => {
    await createDialog();
    selectManualEntry();
    dialog.teamForm.patchValue({teamNumber: '3'});
    expect((await dialog.save()).teamNumber).toBe(3);
  });

  it('should only be valid with a selected player', async () => {
    await createDialog();
    expect(dialog.isValid).toBeFalse();
    selectDatabaseEntry();
    expect(dialog.isValid).toBeTrue();
  });

  describe('validation', () => {
    const CURRENT_YEAR = new Date().getFullYear();
    const yearOfBirth = (value: string | number) => {
      dialog.form.controls.yearOfBirth.setValue(value);
      return dialog.isValid;
    };
    const gender = (value: string) => {
      dialog.form.controls.gender.setValue(value);
      return dialog.isValid;
    };
    // Bootstrap only displays the messages next to an input marked as invalid.
    const visibleErrors = (): string[] => {
      fixture.detectChanges();
      return Array.from<HTMLElement>(fixture.nativeElement.querySelectorAll('.is-invalid ~ .invalid-feedback')).map(e => e.textContent!.trim());
    };

    beforeEach(async () => {
      await createDialog();
      selectManualEntry();
    });

    it('should accept empty fields', () => {
      expect(dialog.isValid).toBeTrue();
    });

    it('should accept plausible years', () => {
      expect(yearOfBirth('1900')).toBeTrue();
      expect(yearOfBirth(String(CURRENT_YEAR))).toBeTrue();
    });

    it('should reject implausible years', () => {
      for (const value of ['abc', '12', '1899', String(CURRENT_YEAR + 1), '19.5']) {
        expect(yearOfBirth(value)).withContext(value).toBeFalse();
      }
    });

    it('should accept m, w and d in any case', () => {
      for (const value of ['m', 'w', 'd', 'M', 'W', 'D', ' w ']) {
        expect(gender(value)).withContext(value).toBeTrue();
      }
    });

    it('should reject other genders', () => {
      for (const value of ['x', 'mw', 'männlich']) {
        expect(gender(value)).withContext(value).toBeFalse();
      }
    });

    it('should not show an error before the field is left', () => {
      yearOfBirth('12');
      gender('x');
      expect(visibleErrors()).toEqual([]);
    });

    it('should show the errors after leaving the fields', () => {
      yearOfBirth('12');
      gender('x');
      dialog.form.markAllAsTouched();
      expect(visibleErrors()).toEqual([`Bitte ein Jahr zwischen 1900 und ${CURRENT_YEAR} angeben`, 'Bitte m, w oder d angeben']);
    });

    it('should show the errors when pressing enter', () => {
      yearOfBirth('12');
      fixture.detectChanges();
      fixture.nativeElement.querySelector('nsv-form:last-of-type').dispatchEvent(new KeyboardEvent('keyup', {key: 'Enter'}));
      expect(visibleErrors().length).toBe(1);
      expect(dialog.modal.close).not.toHaveBeenCalled();
    });

    it('should not show any errors for a database player', () => {
      gender('x');
      dialog.form.markAllAsTouched();
      selectDatabaseEntry();
      expect(visibleErrors()).toEqual([]);
      expect(dialog.isValid).toBeTrue();
    });

    it('should normalize a valid gender', async () => {
      gender(' W ');
      expect((await dialog.save()).player.gender).toBe('w');
    });
  });

  it('should not ask for any data of a database player', async () => {
    await createDialog();
    selectDatabaseEntry();
    expect(fieldCount()).toBe(0);
  });

  it('should only ask for year of birth and gender of a manual entry', async () => {
    await createDialog();
    selectManualEntry();
    expect(fieldCount()).toBe(2);
  });

  it('should return the data of a database player', async () => {
    await createDialog();
    selectDatabaseEntry();
    expect((await dialog.save()).player).toEqual({
      firstName: 'Marcel',
      lastName: 'Jünemann',
      title: 'FM',
      zps: '70156-0116',
      dwz: 1600,
      elo: 1750,
      yearOfBirth: 2000,
      gender: 'm',
    });
  });

  it('should return the data of a manual entry', async () => {
    await createDialog();
    selectManualEntry();
    dialog.form.patchValue({yearOfBirth: '1990', gender: 'W'});
    expect((await dialog.save()).player).toEqual({firstName: 'Nora', lastName: 'Neumann', yearOfBirth: 1990, gender: 'w'});
  });

  it('should close with the result without saving anything else', async () => {
    await createDialog();
    selectManualEntry();
    dialog.saveDialog();
    await fixture.whenStable();
    expect(dialog.modal.close).toHaveBeenCalledOnceWith(jasmine.objectContaining({teamNumber: 2}));
  });
});
