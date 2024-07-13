import { Component, EventEmitter, Output } from '@angular/core';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { Observable, of } from 'rxjs';
import { debounceTime, switchMap } from 'rxjs/operators';
import { FormsModule } from '@angular/forms';
import { DwzPlayer, DwzService } from '../dwz.service';

@Component({
  selector: 'dwz-player-search',
  standalone: true,
	imports: [NgbTypeaheadModule, FormsModule],
  templateUrl: './player-search.component.html',
  styleUrl: './player-search.component.css'
})
export class PlayerSearchComponent {
	player: DwzPlayer|undefined
  @Output() playerSelected = new EventEmitter<DwzPlayer|undefined>();

  constructor(private dwz: DwzService) {}

	search = (text$: Observable<string>) => {
		return text$.pipe(
			debounceTime(200),
			switchMap((term: string) => {
				return term === '' ? of([]) : this.dwz.findPlayer(term, '')
      }),
		)
  }

	formatter = (player: DwzPlayer) => player.name
}
