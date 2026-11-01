import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { DIALOG_PARAMS } from '../../core/dialog/dialog';
import { DialogService } from '../../core/dialog/dialog.service';
import { LeagueService } from '../league.service';
import { LateRegistration, Pairing, Team } from '../types';
import { LateRegistrationDialog } from './late-registration-dialog.component';
import { PairingDialog } from './pairing-dialog.component';

const team = (id: number, name: string, zps: string, firstPlayerId: number): Team => ({
  id, name, zps,
  playersByTeamNumber: {
    2: [{id: firstPlayerId, name: 'Eins, Erika', number: 1, dwz: 1800}, {id: firstPlayerId + 1, name: 'Zwei, Zoe', number: 2, dwz: 1700}],
    3: [{id: firstPlayerId + 2, name: 'Drei, Dora', number: 3, dwz: 1600}],
  },
})

const PAIRING = {id: 1, team1: {id: 10, name: 'Heim 2'}, team2: {id: 20, name: 'Gast 2'}, comment: null, result1: null, result2: null, games: null} as Pairing
const LATE_REGISTRATION: LateRegistration = {teamNumber: 3, player: {firstName: 'Nora', lastName: 'Neumann'}}

describe('PairingDialog', () => {
  let fixture: ComponentFixture<PairingDialog>;
  let dialog: PairingDialog;
  let dialogService: jasmine.SpyObj<DialogService>;

  beforeEach(async () => {
    const leagueService = jasmine.createSpyObj('LeagueService', ['getTeam']);
    leagueService.getTeam.and.callFake((id: number) => Promise.resolve(id === 10 ? team(10, 'Heim 2', '70156', 1) : team(20, 'Gast 2', '70157', 11)));
    dialogService = jasmine.createSpyObj('DialogService', ['open']);

    await TestBed.configureTestingModule({
      providers: [
        { provide: LeagueService, useValue: leagueService },
        { provide: DialogService, useValue: dialogService },
        { provide: NgbActiveModal, useValue: jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']) },
        { provide: DIALOG_PARAMS, useValue: {pairingId: 1, matchDay: {pairings: [PAIRING]}, divisions: [], teams: [], boardCount: 2} },
      ],
      imports: [PairingDialog],
    })
    .compileComponents();

    fixture = TestBed.createComponent(PairingDialog);
    dialog = fixture.componentInstance;
    await fixture.whenStable();
    fixture.detectChanges();
  });

  const playerSelect = (index: number): HTMLSelectElement => fixture.nativeElement.querySelectorAll('.col-sm select')[index];
  const optionTexts = (select: HTMLSelectElement) => Array.from(select.options).map(option => option.text.trim());
  const choose = (select: HTMLSelectElement, text: string) => {
    select.selectedIndex = optionTexts(select).indexOf(text);
    select.dispatchEvent(new Event('change'));
  };
  const chooseLateRegistration = async (select: HTMLSelectElement, result: Promise<LateRegistration>) => {
    dialogService.open.and.returnValue({result} as any);
    choose(select, 'Nachmeldung...');
    await result.catch(() => {});
    await fixture.whenStable();
    fixture.detectChanges();
  };

  it('should offer a single late registration option at the end', () => {
    expect(optionTexts(playerSelect(0))).toEqual(['1 Eins, Erika', '2 Zwei, Zoe', '3 Drei, Dora', '', 'Nachmeldung...']);
  });

  it('should open the dialog with the teams of that side', async () => {
    await chooseLateRegistration(playerSelect(1), Promise.resolve(LATE_REGISTRATION));
    expect(dialogService.open).toHaveBeenCalledOnceWith(LateRegistrationDialog, {
      teamName: 'Gast 2',
      teamNumbers: [2, 3],
      preferredZps: '70157',
    });
  });

  it('should show a confirmed late registration by name only', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.resolve(LATE_REGISTRATION));
    expect(dialog.editor.boardRows[0].player1.value).toBe(LATE_REGISTRATION);
    expect(playerSelect(0).selectedOptions[0].text.trim()).toBe('Neumann, Nora');
  });

  it('should list a late registration at the end of its team', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.resolve({...LATE_REGISTRATION, teamNumber: 2}));
    expect(optionTexts(playerSelect(0))).toEqual(['1 Eins, Erika', '2 Zwei, Zoe', 'Neumann, Nora', '3 Drei, Dora', '', 'Nachmeldung...']);
  });

  it('should list a late registration for the last team before the empty option', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.resolve(LATE_REGISTRATION));
    expect(optionTexts(playerSelect(0))).toEqual(['1 Eins, Erika', '2 Zwei, Zoe', '3 Drei, Dora', 'Neumann, Nora', '', 'Nachmeldung...']);
  });

  it('should offer a late registration on the other boards of that side only', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.resolve(LATE_REGISTRATION));
    expect(optionTexts(playerSelect(2))).toContain('Neumann, Nora');
    expect(optionTexts(playerSelect(1))).not.toContain('Neumann, Nora');
  });

  it('should keep offering a late registration after choosing another player', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.resolve(LATE_REGISTRATION));
    choose(playerSelect(0), '2 Zwei, Zoe');
    fixture.detectChanges();
    expect(optionTexts(playerSelect(0))).toContain('Neumann, Nora');
  });

  it('should restore the previous selection when the dialog is cancelled', async () => {
    await chooseLateRegistration(playerSelect(0), Promise.reject());
    expect(dialog.editor.boardRows[0].player1.value).toBe(1);
    expect(dialog.editor.boardRows[0].player1.isManual).toBeFalse();
    expect(playerSelect(0).selectedOptions[0].text.trim()).toBe('1 Eins, Erika');
  });

  it('should not guess anything until the dialog is confirmed', () => {
    dialogService.open.and.returnValue({result: new Promise(() => {})} as any);
    const secondBoard = dialog.editor.boardRows[1].player1.value;
    choose(playerSelect(0), 'Nachmeldung...');
    expect(dialog.editor.boardRows[1].player1.value).toBe(secondBoard);
  });
});
