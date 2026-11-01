import { Component, Input } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { catchError, map, Observable, of, switchMap } from 'rxjs';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { DwzPlayer, DwzService } from '../../../dwz/dwz.service';

export type PlayerOption = {name: string, data?: DwzPlayer}

/**
 * Name input with autocomplete from the DWZ database.
 *
 * The first instance within a modal receives the initial focus.
 */
@Component({
    selector: 'league-player-select',
    imports: [ReactiveFormsModule, NgbTypeaheadModule],
    templateUrl: './player-select.component.html',
    styleUrl: './player-select.component.css'
})
export class PlayerSelectComponent {
  private static nextId = 0

  // The selected database entry, or the player name in case of manual input.
  @Input({required: true}) control: FormControl<PlayerOption | null>

  // If set, players with this ZPS will be suggested first.
  @Input() preferredZps: string | null = null

  inputId = `league-player-select-${PlayerSelectComponent.nextId++}`

  constructor(private dwz: DwzService) {}

  search = (text$: Observable<string>) => {
    return text$.pipe(
      switchMap((term: string) => {
        const options = term === '' ? of([]) : this.dwz.findPlayer(term, this.preferredZps || undefined)
        return options.pipe(map((players: DwzPlayer[]) => {
          const options: PlayerOption[] = players.map(p => ({name: p.name, data: p}))
          // Allow registering a player that isn't in the DWZ database.
          if (term.match(/.+, .+/g)) {
            options.push({name: term})
          }
          return options
        }))
      }),
      catchError(err => {
        console.error('Error generating autocomplete options:', err)
        return of([])
      })
    )
  }
  formatter = (player: PlayerOption) => player.name
}
