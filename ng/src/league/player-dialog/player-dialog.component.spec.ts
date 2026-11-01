import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { By } from '@angular/platform-browser';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { DIALOG_PARAMS } from '../../core/dialog/dialog';
import { DwzPlayer } from '../../dwz/dwz.service';
import { LeagueService } from '../league.service';
import { PlayerSelectComponent } from '../shared/player-select/player-select.component';
import { PlayerDialog, PlayerDialogParams } from './player-dialog.component';

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

const BLANK_FORM = {title: '', zps: '', yearOfBirth: '', gender: '', dwz: '', elo: ''}

const EXISTING_PLAYER = {
  id: 7,
  firstName: 'Max',
  lastName: 'Mustermann',
  title: 'IM',
  yearOfBirth: 1990,
  gender: 'm',
  zps: '12345-0042',
  dwz: 2000,
  elo: 2100,
  lateRegistrationRound: null,
}

describe('PlayerDialog', () => {
  let dialog: PlayerDialog;
  let fixture: ComponentFixture<PlayerDialog>;
  let leagueService: jasmine.SpyObj<LeagueService>;

  const createDialog = async (player?: PlayerDialogParams['player'], overrides: Partial<PlayerDialogParams> = {}) => {
    const params: PlayerDialogParams = {teamId: 1, roundCount: 7, preferredZps: null, currentRound: null, player, ...overrides};
    leagueService = jasmine.createSpyObj('LeagueService', ['createPlayer', 'updatePlayer']);
    leagueService.createPlayer.and.resolveTo();
    leagueService.updatePlayer.and.resolveTo();

    await TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: NgbActiveModal, useValue: jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']) },
        { provide: DIALOG_PARAMS, useValue: params },
        { provide: LeagueService, useValue: leagueService },
      ],
      imports: [PlayerDialog],
    })
    .compileComponents();

    fixture = TestBed.createComponent(PlayerDialog);
    dialog = fixture.componentInstance;
    fixture.detectChanges();
  };

  const selectDatabaseEntry = () => {
    dialog.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
  };
  const selectManualEntry = () => {
    dialog.selectedPlayer.setValue({name: 'Neumann, Nora'});
  };

  it('should fill in the fields from a database entry', async () => {
    await createDialog();
    selectDatabaseEntry();
    expect(dialog.form.getRawValue()).toEqual({
      title: 'FM', zps: '70156-0116', yearOfBirth: 2000, gender: 'M', dwz: 1600, elo: 1750,
    });
  });

  it('should leave the title empty for a database entry without a FIDE title', async () => {
    await createDialog();
    dialog.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: {...TEST_DWZ_PLAYER, fideTitle: null}});
    expect(dialog.form.controls.title.value).toBe('');
  });

  it('should clear fields from a previous search for a manual entry', async () => {
    await createDialog();
    selectDatabaseEntry();
    selectManualEntry();
    expect(dialog.form.getRawValue()).toEqual(BLANK_FORM);
  });

  it('should keep only the fields matching the parameters for a manual entry', async () => {
    await createDialog({...EXISTING_PLAYER, dwz: TEST_DWZ_PLAYER.dwz});
    selectDatabaseEntry();
    selectManualEntry();
    expect(dialog.form.getRawValue()).toEqual({...BLANK_FORM, dwz: 1600});
  });

  it('should keep the parameters when correcting the name manually', async () => {
    await createDialog(EXISTING_PLAYER);
    selectManualEntry();
    expect(dialog.form.controls.zps.value).toBe('12345-0042');
    expect(dialog.form.controls.dwz.value).toBe(2000);
  });

  it('should clear fields changed by the user for a manual entry', async () => {
    await createDialog(EXISTING_PLAYER);
    dialog.form.controls.dwz.setValue('1650');
    selectManualEntry();
    expect(dialog.form.controls.dwz.value).toBe('');
    expect(dialog.form.controls.elo.value).toBe(2100);
  });

  it('should not clear fields while the name is being changed', async () => {
    await createDialog();
    selectDatabaseEntry();
    dialog.selectedPlayer.setValue(null);
    expect(dialog.form.controls.zps.value).toBe('70156-0116');
  });

  it('should clear fields if the name was changed before selecting a manual entry', async () => {
    await createDialog();
    selectDatabaseEntry();
    dialog.selectedPlayer.setValue(null);
    selectManualEntry();
    expect(dialog.form.getRawValue()).toEqual(BLANK_FORM);
  });

  it('should replace the fields when selecting another database entry', async () => {
    await createDialog(EXISTING_PLAYER);
    selectDatabaseEntry();
    selectManualEntry();
    selectDatabaseEntry();
    expect(dialog.form.controls.zps.value).toBe('70156-0116');
  });

  describe('save', () => {
    const savedData = () => leagueService.createPlayer.calls.mostRecent().args[1];

    it('should create a new player on the team', async () => {
      await createDialog();
      selectManualEntry();
      await dialog.save();
      expect(leagueService.createPlayer).toHaveBeenCalledOnceWith(1, jasmine.anything());
      expect(leagueService.updatePlayer).not.toHaveBeenCalled();
    });

    it('should update an existing player', async () => {
      await createDialog(EXISTING_PLAYER);
      await dialog.save();
      expect(leagueService.updatePlayer).toHaveBeenCalledOnceWith(1, 7, jasmine.anything());
      expect(leagueService.createPlayer).not.toHaveBeenCalled();
    });

    it('should send the data of a database entry', async () => {
      await createDialog();
      selectDatabaseEntry();
      await dialog.save();
      expect(savedData()).toEqual({
        firstName: 'Marcel',
        lastName: 'Jünemann',
        title: 'FM',
        zps: '70156-0116',
        dwz: 1600,
        elo: 1750,
        yearOfBirth: 2000,
        gender: 'm',
        lateRegistrationRound: null,
      });
    });

    it('should send empty optional fields for a manual entry', async () => {
      await createDialog();
      selectManualEntry();
      await dialog.save();
      expect(savedData()).toEqual({
        firstName: 'Nora',
        lastName: 'Neumann',
        title: '',
        zps: '',
        dwz: null,
        elo: null,
        yearOfBirth: null,
        gender: '',
        lateRegistrationRound: null,
      });
    });

    it('should not send data from a previous search for a manual entry', async () => {
      await createDialog();
      selectDatabaseEntry();
      selectManualEntry();
      await dialog.save();
      expect(savedData()).toEqual(jasmine.objectContaining({
        lastName: 'Neumann', title: '', zps: '', dwz: null, elo: null, yearOfBirth: null, gender: '',
      }));
    });

    it('should convert values entered in the form', async () => {
      await createDialog();
      selectManualEntry();
      dialog.form.patchValue({title: ' FM ', dwz: '1650', gender: 'W'});
      dialog.registrationForm.patchValue({lateRegistrationRound: '3'});
      await dialog.save();
      expect(savedData()).toEqual(jasmine.objectContaining({
        title: 'FM', dwz: 1650, gender: 'w', lateRegistrationRound: 3,
      }));
    });
  });

  describe('player selection', () => {
    const playerSelect = (): PlayerSelectComponent => {
      return fixture.debugElement.query(By.directive(PlayerSelectComponent)).componentInstance;
    };

    it('should pass the selected player control', async () => {
      await createDialog();
      expect(playerSelect().control).toBe(dialog.selectedPlayer);
    });

    it('should pass the preferred ZPS', async () => {
      await createDialog(undefined, {preferredZps: '70156'});
      expect(playerSelect().preferredZps).toBe('70156');
    });

    for (const field of ['lastName', 'firstName']) {
      it(`should show server errors for ${field}`, async () => {
        await createDialog();
        dialog.errors = {errorType: 'nsv', errorMessages: [], validationErrors: {[field]: [{message: 'Name fehlt'}]}};
        fixture.detectChanges();
        expect(fixture.nativeElement.querySelector('.text-danger').textContent).toContain('Name fehlt');
      });
    }
  });

  describe('isValid', () => {
    it('should be false without a selected player', async () => {
      await createDialog();
      expect(dialog.isValid).toBeFalse();
    });

    it('should be true with a selected player', async () => {
      await createDialog();
      selectManualEntry();
      expect(dialog.isValid).toBeTrue();
    });

    it('should be false with an invalid number', async () => {
      await createDialog();
      selectManualEntry();
      dialog.form.controls.dwz.setValue('abc');
      expect(dialog.isValid).toBeFalse();
    });
  });

  describe('late registration', () => {
    const round = () => dialog.registrationForm.controls.lateRegistrationRound;

    it('should offer the regular option and one option per round', async () => {
      await createDialog(undefined, {roundCount: 3});
      expect(round().options?.map(option => option.value)).toEqual(['', '1', '2', '3']);
    });

    it('should default to a regular player', async () => {
      await createDialog();
      expect(round().value).toBe('');
    });

    it('should preselect the current round for a new player', async () => {
      await createDialog(undefined, {currentRound: 4});
      expect(round().value).toBe('4');
    });

    it('should use the round of an existing player', async () => {
      await createDialog({...EXISTING_PLAYER, lateRegistrationRound: 2}, {currentRound: 4});
      expect(round().value).toBe('2');
    });

    it('should not apply the current round to a regular existing player', async () => {
      await createDialog(EXISTING_PLAYER, {currentRound: 4});
      expect(round().value).toBe('');
    });
  });

  describe('title', () => {
    const heading = () => fixture.nativeElement.querySelector('.modal-title').textContent;

    it('should offer late registration for a new player', async () => {
      await createDialog();
      expect(heading()).toContain('Spieler:in nachmelden');
    });

    it('should offer changing the data of an existing player', async () => {
      await createDialog(EXISTING_PLAYER);
      expect(heading()).toContain('Spielerdaten ändern');
    });
  });
});
