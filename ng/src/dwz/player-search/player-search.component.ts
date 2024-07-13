import { Component } from '@angular/core';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { Observable, of, OperatorFunction } from 'rxjs';
import { debounceTime, map, switchMap } from 'rxjs/operators';
import { FormsModule } from '@angular/forms';
import { JsonPipe } from '@angular/common';
import { DwzService } from '../dwz.service';

@Component({
  selector: 'dwz-player-search',
  standalone: true,
	imports: [NgbTypeaheadModule, FormsModule, JsonPipe],
  templateUrl: './player-search.component.html',
  styleUrl: './player-search.component.css'
})
export class PlayerSearchComponent {
	model: any;

  constructor(private dwz: DwzService) {}

	search: OperatorFunction<string, any> = (text$: Observable<string>) =>
		text$.pipe(
			debounceTime(200),
			switchMap((term) =>
				term === ''
					? of([])
					: this.dwz.findPlayer(term, '')
			),
		);

	formatter = (x: { name: string }) => x.name;
}
