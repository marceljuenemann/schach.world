import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PlayerDataComponent, PlayerData } from './player-data.component';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { DwzPlayer, DwzService } from '../../dwz/dwz.service';
import { of, throwError } from 'rxjs';

const TEST_DWZ_PLAYER: DwzPlayer = {
  name: 'Jünemann, Marcel',
  club: 'SK Lehrte',
  zps: '70156',
  memberId: '0116',
  status: 'A',
  gender: 'M',
  yearOfBirth: 2000,
  dwz: 1600,
  elo: 1750,
  fideTitle: null,
  fideId: 42943253,
  fideCountry: 'GER'
}

const TEST_PLAYER_DATA: PlayerData = {
  name: 'Jünemann, Marcel',
  club: 'SK Lehrte',
  zps: '70156',
  memberId: '0116',
  gender: 'M',
  yearOfBirth: 2000,
  dwz: 1600,
  elo: 1750,
  fideTitle: null,
  fideId: 42943253
}

describe('PlayerDataComponent', () => {
  let component: PlayerDataComponent;
  let fixture: ComponentFixture<PlayerDataComponent>;
  let dwzService: jasmine.SpyObj<DwzService>;

  beforeEach(async () => {
    dwzService = jasmine.createSpyObj('DwzService', ['findPlayer', 'findClub']);
    dwzService.findPlayer.and.returnValue(of([]));
    dwzService.findClub.and.returnValue(of([]));

    await TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: DwzService, useValue: dwzService }
      ],
      imports: [PlayerDataComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(PlayerDataComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  describe('visibleControls', () => {
    it('returns null when restrictEditing is false', () => {
      component.restrictEditing = false;
      expect(component.visibleControls).toBeNull();
    });

    it('returns [dwz, elo] when no player is selected', () => {
      component.restrictEditing = true;
      expect(component.visibleControls).toEqual([
        component.form.controls.dwz,
        component.form.controls.elo
      ]);
    });

    it('returns [yearOfBirth, gender] for manual entry', () => {
      component.restrictEditing = true;
      component.selectedPlayer.setValue({name: 'Doe, John'});
      expect(component.visibleControls).toEqual([
        component.form.controls.yearOfBirth,
        component.form.controls.gender
      ]);
    });

    it('returns [dwz, elo] when a DWZ player is selected', () => {
      component.restrictEditing = true;
      component.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
      expect(component.visibleControls).toEqual([
        component.form.controls.dwz,
        component.form.controls.elo
      ]);
    });
  });

  describe('initialPlayerData', () => {
    it('does nothing when called with undefined', () => {
      component.initialPlayerData = undefined;
      expect(component.editing).toBe(false);
    });

    it('sets selected player name, club, and editing flag', () => {
      component.initialPlayerData = TEST_PLAYER_DATA;
      expect(component.selectedPlayer.value).toEqual({name: 'Jünemann, Marcel'});
      expect(component.club.value).toBe('SK Lehrte');
      expect(component.editing).toBe(true);
    });
  });

  describe('search', () => {
    it('returns empty array for empty term without calling the DWZ service', () => {
      let result: any;
      component.search(of('')).subscribe(r => result = r);
      expect(result).toEqual([]);
      expect(dwzService.findPlayer).not.toHaveBeenCalled();
    });

    it('returns mapped DWZ player options', () => {
      dwzService.findPlayer.and.returnValue(of([TEST_DWZ_PLAYER]));
      let result: any;
      component.search(of('Jüne')).subscribe(r => result = r);
      expect(result).toEqual([{name: 'Jünemann, Marcel', data: TEST_DWZ_PLAYER}]);
    });

    it('adds a manual entry option when term matches "Lastname, Firstname" format', () => {
      dwzService.findPlayer.and.returnValue(of([]));
      let result: any;
      component.search(of('Doe, John')).subscribe(r => result = r);
      expect(result).toContain(jasmine.objectContaining({name: 'Doe, John'}));
    });

    it('does not add a manual entry option for a term without a comma', () => {
      dwzService.findPlayer.and.returnValue(of([]));
      let result: any;
      component.search(of('Doe')).subscribe(r => result = r);
      expect(result).toEqual([]);
    });

    it('returns empty array when the DWZ service throws an error', () => {
      dwzService.findPlayer.and.returnValue(throwError(() => new Error('Network error')));
      let result: any;
      component.search(of('Jüne')).subscribe(r => result = r);
      expect(result).toEqual([]);
    });
  });

  describe('player selection', () => {
    it('populates club and form when a DWZ player is selected', () => {
      component.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
      expect(component.club.value).toBe('SK Lehrte');
      expect(component.form.controls.dwz.value).toBe(1600);
    });

    it('resets club and form when player is deselected while not editing', () => {
      component.selectedPlayer.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
      component.selectedPlayer.setValue(null);
      expect(component.club.value).toBeNull();
      expect(component.form.controls.dwz.value).toBeNull();
    });

    it('does not reset club when player is deselected while editing', () => {
      component.initialPlayerData = TEST_PLAYER_DATA;
      component.selectedPlayer.setValue(null);
      expect(component.club.value).toBe('SK Lehrte');
    });
  });
});
