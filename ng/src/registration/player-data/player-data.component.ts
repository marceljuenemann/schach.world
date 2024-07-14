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
	// selectedPlayer = model<PlayerData|string>('')

  form = new FormGroup({
    selectedPlayer: new FormControl<PlayerData|string>(''),
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

  @Output() playerSelected = new EventEmitter<PlayerData|undefined>();

  constructor(private dwz: DwzService) {}

  ngOnInit() {
    this.form.controls.selectedPlayer.valueChanges.subscribe(player => {
      if (player && typeof player === 'object') {
        this.form.controls.club.setValue(player.club)
        this.form.controls.zps.setValue(player.zps)
        this.form.controls.memberId.setValue(player.memberId)
      }
    })
  }


	search = (text$: Observable<string>) => {
		return text$.pipe(
			debounceTime(200),
			switchMap((term: string) => {
				return term === '' ? of([]) : this.dwz.findPlayer(term, '')
      }),
		)
  }

	formatter = (player: PlayerData) => player.name
}
