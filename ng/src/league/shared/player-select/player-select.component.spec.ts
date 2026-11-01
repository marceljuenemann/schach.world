import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FormControl } from '@angular/forms';
import { firstValueFrom, of, throwError } from 'rxjs';

import { DwzPlayer, DwzService } from '../../../dwz/dwz.service';
import { PlayerOption, PlayerSelectComponent } from './player-select.component';

const TEST_DWZ_PLAYER = {name: 'Jünemann, Marcel', club: 'SK Lehrte'} as DwzPlayer

describe('PlayerSelectComponent', () => {
  let component: PlayerSelectComponent;
  let fixture: ComponentFixture<PlayerSelectComponent>;
  let dwzService: jasmine.SpyObj<DwzService>;

  const search = (term: string) => firstValueFrom(component.search(of(term)))

  beforeEach(async () => {
    dwzService = jasmine.createSpyObj('DwzService', ['findPlayer']);
    dwzService.findPlayer.and.returnValue(of([TEST_DWZ_PLAYER]));

    await TestBed.configureTestingModule({
      providers: [
        { provide: DwzService, useValue: dwzService }
      ],
      imports: [PlayerSelectComponent],
    })
    .compileComponents();

    fixture = TestBed.createComponent(PlayerSelectComponent);
    component = fixture.componentInstance;
    fixture.componentRef.setInput('control', new FormControl<PlayerOption | null>(null));
    fixture.detectChanges();
  });

  it('should bind the label to the input', () => {
    const input = fixture.nativeElement.querySelector('input');
    const label = fixture.nativeElement.querySelector('label');
    expect(label.htmlFor).toBe(input.id);
  });

  it('should use a unique input id per instance', () => {
    const other = TestBed.createComponent(PlayerSelectComponent);
    expect(other.componentInstance.inputId).not.toBe(component.inputId);
  });

  it('should show the current value of the control', () => {
    component.control.setValue({name: 'Mustermann, Max'});
    expect(fixture.nativeElement.querySelector('input').value).toBe('Mustermann, Max');
  });

  it('should not search for an empty term', async () => {
    expect(await search('')).toEqual([]);
    expect(dwzService.findPlayer).not.toHaveBeenCalled();
  });

  it('should suggest players from the DWZ database', async () => {
    expect(await search('Jün')).toEqual([{name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER}]);
    expect(dwzService.findPlayer).toHaveBeenCalledWith('Jün', undefined);
  });

  it('should pass the preferred ZPS to the search', async () => {
    fixture.componentRef.setInput('preferredZps', '70156');
    await search('Jün');
    expect(dwzService.findPlayer).toHaveBeenCalledWith('Jün', '70156');
  });

  it('should offer manual entry for names in the format "Nachname, Vorname"', async () => {
    dwzService.findPlayer.and.returnValue(of([]));
    expect(await search('Mustermann, Max')).toEqual([{name: 'Mustermann, Max'}]);
  });

  it('should return no options if the search fails', async () => {
    spyOn(console, 'error');
    dwzService.findPlayer.and.returnValue(throwError(() => new Error('Test')));
    expect(await search('Jün')).toEqual([]);
  });
});
