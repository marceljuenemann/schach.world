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
    expect(await search('Jün')).toEqual([{name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER}, component.formatHintOption]);
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

  it('should correct "Nachname,Vorname" for manual entry', async () => {
    dwzService.findPlayer.and.returnValue(of([]));
    expect(await search(' Mustermann ,Max ')).toEqual([{name: 'Mustermann, Max'}]);
  });

  it('should show the format hint instead of manual entry without a comma', async () => {
    dwzService.findPlayer.and.returnValue(of([]));
    expect(await search('Mustermann')).toEqual([component.formatHintOption]);
    expect(await search('Max Mustermann')).toEqual([component.formatHintOption]);
  });

  it('should search for the corrected name if the input has a comma', async () => {
    await search('Mustermann,Max');
    expect(dwzService.findPlayer.calls.allArgs()).toEqual([['Mustermann, Max', undefined]]);
  });

  it('should search for the input as is if there is no comma', async () => {
    await search('von der Heide');
    expect(dwzService.findPlayer.calls.allArgs()).toEqual([['von der Heide', undefined]]);
  });

  it('should show the format hint instead of manual entry without a first name', async () => {
    dwzService.findPlayer.and.returnValue(of([]));
    expect(await search('Mustermann, ')).toEqual([component.formatHintOption]);
  });

  it('should list the manual entry after the database entries', async () => {
    expect(await search('Jünemann, M')).toEqual([
      {name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER},
      {name: 'Jünemann, M'},
    ]);
  });

  describe('status', () => {
    const focusInput = () => {
      fixture.nativeElement.querySelector('input').dispatchEvent(new Event('focus'));
    };
    const blurInput = () => {
      fixture.nativeElement.querySelector('input').dispatchEvent(new Event('blur'));
    };
    const statusText = () => {
      fixture.detectChanges();
      return fixture.nativeElement.querySelector('.player-select-status')?.textContent.trim();
    };

    it('should not show a status for a player loaded for editing', () => {
      component.control.setValue({name: 'Mustermann, Max'});
      expect(statusText()).toBeUndefined();
      expect(fixture.nativeElement.querySelector('input').classList).not.toContain('is-valid');
    });

    it('should show the club of a selected database entry in green', () => {
      component.control.setValue({name: TEST_DWZ_PLAYER.name, data: TEST_DWZ_PLAYER});
      component.control.markAsDirty();
      expect(statusText()).toContain('SK Lehrte');
      expect(fixture.nativeElement.querySelector('input').classList).toContain('is-valid');
    });

    it('should warn about a manual entry in amber', () => {
      component.control.setValue({name: 'Mustermann, Max'});
      component.control.markAsDirty();
      expect(statusText()).toContain('Nicht in der Mitgliederdatenbank');
      expect(fixture.nativeElement.querySelector('input').classList).toContain('border-warning');
    });

    it('should not show an error while the input is focused', () => {
      component.control.markAsDirty();
      focusInput();
      expect(statusText()).toBeUndefined();
    });

    it('should show an error if the input is left without a selection', () => {
      component.control.markAsDirty();
      focusInput();
      blurInput();
      expect(statusText()).toContain('Bitte wählen Sie einen Eintrag aus der Liste aus');
      expect(fixture.nativeElement.querySelector('input').classList).toContain('is-invalid');
    });
  });

  describe('format hint', () => {
    const type = async (text: string) => {
      const input = fixture.nativeElement.querySelector('input');
      input.dispatchEvent(new Event('focus'));
      input.value = text;
      input.dispatchEvent(new Event('input'));
      fixture.detectChanges();
      await fixture.whenStable();
      return input;
    };
    const rows = (): HTMLElement[] => Array.from(fixture.nativeElement.querySelectorAll('ngb-typeahead-window button'));
    const press = (input: HTMLElement, key: string) => {
      input.dispatchEvent(new KeyboardEvent('keydown', {key, cancelable: true}));
      fixture.detectChanges();
    };

    beforeEach(() => {
      dwzService.findPlayer.and.returnValue(of([]));
    });

    it('should keep the dropdown open without any matches', async () => {
      await type('Max Mustermann');
      expect(rows().length).toBe(1);
      expect(rows()[0].textContent).toContain('Nachname, Vorname');
    });

    it('should be listed after the matches', async () => {
      dwzService.findPlayer.and.returnValue(of([TEST_DWZ_PLAYER]));
      await type('Jün');
      expect(rows().length).toBe(2);
      expect(rows()[1].textContent).toContain('Nachname, Vorname');
    });

    it('should not appear once the format matches', async () => {
      await type('Mustermann, Max');
      expect(fixture.nativeElement.querySelector('.format-hint')).toBeNull();
    });

    it('should not select anything on click', async () => {
      await type('Max Mustermann');
      rows()[0].click();
      expect(component.control.value).toBeNull();
    });

    it('should not select anything with Enter if it is the only entry', async () => {
      const input = await type('Max Mustermann');
      press(input, 'Enter');
      expect(component.control.value).toBeNull();
      expect(input.value).toBe('Max Mustermann');
    });

    it('should not select anything with the keyboard', async () => {
      const input = await type('Max Mustermann');
      press(input, 'ArrowDown');
      press(input, 'Enter');
      expect(component.control.value).toBeNull();
      expect(input.value).toBe('Max Mustermann');
    });

    it('should still select the first match with Enter', async () => {
      dwzService.findPlayer.and.returnValue(of([TEST_DWZ_PLAYER]));
      const input = await type('Jün');
      press(input, 'Enter');
      expect(component.control.value?.data).toBe(TEST_DWZ_PLAYER);
    });

    it('should select the first match with Enter after the hint was the only entry', async () => {
      await type('Jün');
      dwzService.findPlayer.and.returnValue(of([TEST_DWZ_PLAYER]));
      const input = await type('Jünemann');
      press(input, 'Enter');
      expect(component.control.value?.data).toBe(TEST_DWZ_PLAYER);
    });
  });

  it('should return no options if the search fails', async () => {
    spyOn(console, 'error');
    dwzService.findPlayer.and.returnValue(throwError(() => new Error('Test')));
    expect(await search('Jün')).toEqual([]);
  });
});
