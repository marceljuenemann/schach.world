import { Component, Input } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { catchError, map, Observable, of, switchMap } from 'rxjs';
import { NgbTypeaheadModule, NgbTypeaheadSelectItemEvent } from '@ng-bootstrap/ng-bootstrap';
import { DwzPlayer, DwzService } from '../../../dwz/dwz.service';

export type PlayerOption = {name: string, data?: DwzPlayer}

type PlayerSelectStatus = 'none' | 'unselected' | 'database' | 'manual'

// Dropdown entry for the format hint. It can't be selected.
const FORMAT_HINT_OPTION: PlayerOption = {name: ''}

/**
 * Converts user input to "Lastname, Firstname". Returns null if the input
 * isn't in that format yet.
 */
function normalizeName(input: string): string | null {
  const term = input.replace(/\s+/g, ' ')
  const commaIndex = term.indexOf(',')
  if (commaIndex < 0) return null
  const lastName = term.slice(0, commaIndex).trim()
  const firstName = term.slice(commaIndex + 1).trim()
  return lastName && firstName ? `${lastName}, ${firstName}` : null
}

/**
 * Name input with autocomplete from the DWZ database.
 *
 * Shows a status below the input, but only once the user has changed the
 * selection, so a player loaded for editing doesn't look like a new manual entry.
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
  focused = false
  formatHintOption = FORMAT_HINT_OPTION

  constructor(private dwz: DwzService) {}

  get status(): PlayerSelectStatus {
    const player = this.control.value
    if (player) {
      if (!this.control.dirty) return 'none'
      return player.data ? 'database' : 'manual'
    }
    return this.control.dirty && !this.focused ? 'unselected' : 'none'
  }

  search = (text$: Observable<string>) => {
    return text$.pipe(
      switchMap((term: string) => {
        const manualName = normalizeName(term)
        const options = term === '' ? of([]) : this.dwz.findPlayer(manualName ?? term, this.preferredZps || undefined)
        return options.pipe(map((players: DwzPlayer[]) => {
          const options: PlayerOption[] = players.map(p => ({name: p.name, data: p}))
          // Allow registering a player that isn't in the DWZ database.
          if (manualName) {
            options.push({name: manualName})
          } else if (term !== '') {
            options.push(FORMAT_HINT_OPTION)
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

  onSelectItem(event: NgbTypeaheadSelectItemEvent<PlayerOption>) {
    if (event.item === FORMAT_HINT_OPTION) {
      event.preventDefault()
    }
  }
}
