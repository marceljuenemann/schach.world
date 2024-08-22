import { Component, Input } from '@angular/core';
import { DwzPlayer, DwzService, DwzClub } from '../../dwz/dwz.service';
import { combineLatest, map, Observable, of, switchMap, zip } from 'rxjs';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { outputFromObservable } from '@angular/core/rxjs-interop';
import { ValidationErrors } from '../../core/api';

export type PlayerData = Omit<DwzPlayer, 'status' | 'gender' | 'yearOfBirth' | 'fideCountry'> & {
  gender: 'X' | 'M' | 'D' | null
  yearOfBirth: number | null
}

type PlayerOption = {name: string, data?: DwzPlayer}

const CONTROL_OPTIONS = {
  zps: {label: 'Vereins-Nr.'},
  memberId: {label: 'Mitglieds-Nr.'},
  yearOfBirth: {label: 'Geburtsjahr'},
  gender: {label: 'Geschlecht (M/W/D)'},
  dwz: {label: 'DWZ'},
  elo: {label: 'ELO'},
  fideId: {label: 'FIDE-ID'},
  fideTitle: {label: 'FIDE-Titel'},
}

@Component({
  selector: 'player-data',
  standalone: true,
  imports: [FormsModule, ReactiveFormsModule, NgbTypeaheadModule],
  templateUrl: './player-data.component.html',
  styleUrl: './player-data.component.css'
})
export class PlayerDataComponent {
  private subscription

  @Input()
  validationErrors: ValidationErrors | undefined = undefined

  // The selected database entry, or the player name in case of manual input.
  selectedPlayer = new FormControl<PlayerOption|null>(null)
  validatePlayerSelection = false

  form = new FormGroup({
    club: new FormControl(''),
    zps: new FormControl(''),
    memberId: new FormControl(''),
    yearOfBirth: new FormControl(''),
    gender: new FormControl(''),
    dwz: new FormControl(''),
    elo: new FormControl(''),
    fideId: new FormControl(''),
    fideTitle: new FormControl(''),
  });

  onPlayerDataChange = outputFromObservable<PlayerData|null>(
    combineLatest([this.selectedPlayer.valueChanges, this.form.valueChanges])
    .pipe(map(([selectedPlayer, formData]) => {
      if (!selectedPlayer || !selectedPlayer.name) return null
      return {
        name: selectedPlayer.name,
        club: formData.club || '',
        zps: formData.zps || '',
        memberId: formData.memberId || '',
        gender: (formData.gender?.toUpperCase() || null) as any,
        yearOfBirth: parseInt(formData.yearOfBirth!) || null,
        dwz: parseInt(formData.dwz!) || null,
        elo: parseInt(formData.elo!) || null,
        fideTitle: (formData.fideTitle as any) || null,
        fideId: parseInt(formData.fideId!) || null
      }
    })))

  constructor(private dwz: DwzService) {
    this.form.disable()
    this.subscription = this.selectedPlayer.valueChanges.subscribe(player => {
      if (player && player.data) {
        // Player was selected from the database.
        this.form.patchValue(player.data as any)
        this.form.disable()
      } else if (player) {
        // Player name was entered manually.
        this.form.enable()
      } else {
        // No player selected.
        this.form.reset()
        this.form.disable()
      }
    })
  }

  get isManualEntry() {
    return this.selectedPlayer.value && !this.selectedPlayer.value.data
  }

  get visibleControls() {
    if (this.isManualEntry) {
      return ['yearOfBirth', 'gender']
    } else {
      return ['dwz', 'elo']
    }
  }

  controlLabel = (id: string) => (CONTROL_OPTIONS as any)[id].label

	search = (text$: Observable<string>) => {
		return text$.pipe(
			switchMap((term: string) => {
        // Get suggestions based on the term.
				const options = term === '' ? of([]) : this.dwz.findPlayer(term, '')
        return options.pipe(map((players: DwzPlayer[]) => {
          const options: PlayerOption[] = players.map(p => { return{name: p.name, data: p} })
          // Possibly add option for manual entry.
          if (term.match(/.+, .+/g)) {
            options.push({name: term})
          }
          return options
        }))
      })
		)
  }
	formatter = (player: PlayerOption) => player.name

	searchClub = (text$: Observable<string>) => {
		return text$.pipe(
			switchMap((term: string) => term === '' ? of([]) : this.dwz.findClub(term, '')),
      map((clubs: DwzClub[]) => clubs.map(club => club.name))
		)
  }

  ngOnDestroy() {
    this.subscription.unsubscribe()
  }
}
