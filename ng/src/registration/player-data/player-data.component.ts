import { model, Component, EventEmitter, Output, OnInit } from '@angular/core';
import { PlayerData, DwzService } from '../../dwz/dwz.service';
import { debounceTime, Observable, of, switchMap } from 'rxjs';
import { NgbTypeahead, NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, FormGroupDirective, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { JsonPipe } from '@angular/common';

@Component({
  selector: 'player-data',
  standalone: true,
  imports: [FormsModule, ReactiveFormsModule, NgbTypeaheadModule, JsonPipe],
  templateUrl: './player-data.component.html',
  styleUrl: './player-data.component.css'
})
export class PlayerDataComponent implements OnInit {
  // The selected database entry, or the player name in case of manual input.
  selectedPlayer = new FormControl<PlayerData | undefined>(undefined)
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

  controlOptions = [
    {id: 'zps', label: 'Vereins-Nr.'},
    {id: 'memberId', label: 'Mitglieds-Nr.'},
    {id: 'yearOfBirth', label: 'Geburtsjahr'},
    {id: 'gender', label: 'Geschlecht (M/W/D)'},
    {id: 'dwz', label: 'DWZ'},
    {id: 'elo', label: 'ELO'},
    {id: 'fideId', label: 'FIDE-ID'},
    {id: 'fideTitle', label: 'FIDE-Titel'},
  ]

  @Output() playerSelected = new EventEmitter<PlayerData|undefined>();

  constructor(private dwz: DwzService) {}

  ngOnInit() {
    this.selectedPlayer.valueChanges.subscribe(player => {
      if (player) {
        // Player was selected from the database.
        this.selectedPlayer.setErrors(null)
        for (let field in this.form.controls) {
          const control = this.form.get(field)!
          control.setValue((player as any)[field])
          control.disable()
        }
      } else {
        for (let field in this.form.controls) {
          const control = this.form.get(field)!
          control.setValue('')
          control.disable()
        }
      }
    })
  }

	search = (text$: Observable<string>) => {
		return text$.pipe(
			switchMap((term: string) => {
				return term === '' ? of([]) : this.dwz.findPlayer(term, '')
      }),
		)
  }
	formatter = (player: PlayerData) => player.name

  get isValidPlayer() {
    return this.selectedPlayer.value
  }
}
