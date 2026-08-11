import { Component } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { catchError, map, Observable, of, switchMap } from 'rxjs';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { DwzPlayer, DwzService } from '../../dwz/dwz.service';
import { IntControl, NsvFormGroup, SelectControl, TextControl } from '../../core/form/form-group';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';

export interface PlayerDialogParams {
  teamId: number
  roundCount: number
}

type PlayerOption = {name: string, data?: DwzPlayer}

@Component({
    selector: 'league-player-dialog',
    imports: [ReactiveFormsModule, NgbTypeaheadModule, NsvFormComponent, NsvDialogFooterComponent],
    templateUrl: './player-dialog.component.html',
    styleUrl: './player-dialog.component.css'
})
export class PlayerDialog extends NsvDialog<PlayerDialogParams> {
  selectedPlayer = new FormControl<PlayerOption | null>(null)

  form = new NsvFormGroup({
    title: new TextControl('Titel'),
    yearOfBirth: new IntControl('Geburtsjahr'),
    gender: new TextControl('Geschlecht (M/W/D)'),
    zps: new TextControl('Vereins-Nr.'),
    dwz: new IntControl('DWZ'),
    elo: new IntControl('ELO'),
    round: new SelectControl('Nachmeldung', [
      {label: 'Regulärer Spieler', value: ''},
      ...Array.from({length: this.params.roundCount}, (_, i) => ({
        label: `Nachmeldung ${i + 1}. Spieltag`,
        value: String(i + 1),
      })),
    ]),
  })

  constructor(private dwz: DwzService) {
    super()
    this.selectedPlayer.valueChanges.subscribe(player => {
      if (player?.data) {
        this.form.patchValue({
          title: player.data.fideTitle || '',
          yearOfBirth: player.data.yearOfBirth,
          gender: player.data.gender,
          zps: player.data.zps,
          dwz: player.data.dwz,
          elo: player.data.elo,
        })
      }
    })
  }

  search = (text$: Observable<string>) => {
    return text$.pipe(
      switchMap((term: string) => {
        const options = term === '' ? of([]) : this.dwz.findPlayer(term)
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

  // Saving isn't implemented yet — keep the Save button disabled until the
  // next step wires this up to a real save().
  override get isValid() {
    return false
  }

  override save(): Promise<void> {
    return Promise.resolve()
  }
}
