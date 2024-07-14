import { Component, EventEmitter, Output, OnInit, input } from '@angular/core';
import { PlayerData, DwzService } from '../../dwz/dwz.service';
import { map, Observable, of, switchMap } from 'rxjs';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, FormGroupDirective, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { JsonPipe } from '@angular/common';

type PlayerOption = {name: string, data?: PlayerData}

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
  imports: [FormsModule, ReactiveFormsModule, NgbTypeaheadModule, JsonPipe],
  templateUrl: './player-data.component.html',
  styleUrl: './player-data.component.css'
})
export class PlayerDataComponent {
  // The selected database entry, or the player name in case of manual input.
  selectedPlayer = new FormControl<PlayerOption|null>(null)
  validatePlayerSelection = false

  form = new FormGroup({
    // TODO: Add validators for all!
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

  // TODO: remove
  @Output() playerSelected = new EventEmitter<PlayerData|undefined>();

  constructor(private dwz: DwzService) {
    this.form.disable() // TODO: move to updateDisabledState() or something.
    this.selectedPlayer.valueChanges.subscribe(player => {
      this.form.disable()
      if (player && player.data) {
        // Player was selected from the database.
        this.selectedPlayer.setErrors(null)  // TODO: delete
        // TODO: use patch?
        for (let field in this.form.controls) {
          const control = this.form.get(field)!
          control.setValue((player.data as any)[field])
        }
      } else if (player) {
        this.form.enable()
      } else {
        for (let field in this.form.controls) {
          const control = this.form.get(field)!
          control.setValue('')
        }
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
        return options.pipe(map((players: PlayerData[]) => {
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
}
