import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { PlayerDialogComponent, PlayerDialogParams } from './player-dialog.component';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { DIALOG_PARAMS } from '../../core/dialog/dialog';
import { TEST_PLAYER, TournamentBuilder } from '../testing/tournament-builder';
import { Group, Tournament } from '../tournament';

describe('PlayerDialogComponent', () => {
  let component: PlayerDialogComponent;
  let fixture: ComponentFixture<PlayerDialogComponent>;
  let mockActiveModal: jasmine.SpyObj<NgbActiveModal>;
  let mockParams: PlayerDialogParams;
  let tournament: Tournament;

  beforeEach(async () => {
    mockActiveModal = jasmine.createSpyObj('NgbActiveModal', ['close', 'dismiss']);
    tournament = new TournamentBuilder().build();
    mockParams = {
      tournament,
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

  describe('registrationRestriction', () => {
    it('returns null when no player data', () => {
      component.playerData = null;
      expect(component.registrationRestriction(tournament.groups.get('A')!)).toBeNull();
    });

    it('returns restriction for minDwz', () => {
      component.playerData = {...TEST_PLAYER.playerData, dwz: 1600};
      expect(component.registrationRestriction(tournament.groups.get('A')!)).toBe('ab DWZ 1750');
    });

    it('returns null when minDwz is met', () => {
      component.playerData = {...TEST_PLAYER.playerData, dwz: 1800};
      expect(component.registrationRestriction(tournament.groups.get('A')!)).toBeNull();
    });

    it('returns restriction for maxDwz', () => {
      component.playerData = {...TEST_PLAYER.playerData, dwz: 1600};
      expect(component.registrationRestriction(tournament.groups.get('C')!)).toBe('bis DWZ 1500');
    });

    it('returns restriction for minYearOfBirth', () => {
      component.playerData = {...TEST_PLAYER.playerData, yearOfBirth: 2000};
      expect(component.registrationRestriction(tournament.groups.get('U18')!)).toBe('bis Jahrgang 2007');
    });

    it('returns restriction for requireFideId', () => {
      component.playerData = {...TEST_PLAYER.playerData, fideId: null};
      const group = new Group(tournament, {id: 'fide', name: 'FIDE', requireFideId: true});
      expect(component.registrationRestriction(group)).toBe('FIDE-ID erforderlich');
    });
  });
});
