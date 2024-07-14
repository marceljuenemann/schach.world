import { Component, EventEmitter, Output } from '@angular/core';
import { PlayerData, DwzService } from '../../dwz/dwz.service';
import { debounceTime, Observable, of, switchMap } from 'rxjs';
import { NgbTypeahead, NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormsModule } from '@angular/forms';
import { JsonPipe } from '@angular/common';

@Component({
  selector: 'player-data',
  standalone: true,
  imports: [FormsModule, NgbTypeaheadModule, JsonPipe],
  templateUrl: './player-data.component.html',
  styleUrl: './player-data.component.css'
})
export class PlayerDataComponent {
	player: PlayerData|undefined
  @Output() playerSelected = new EventEmitter<PlayerData|undefined>();

  constructor(private dwz: DwzService) {}

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
