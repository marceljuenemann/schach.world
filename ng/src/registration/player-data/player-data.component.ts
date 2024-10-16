import { Component, Input } from '@angular/core';
import { DwzPlayer, DwzService, DwzClub } from '../../dwz/dwz.service';
import { combineLatest, map, Observable, of, switchMap, zip } from 'rxjs';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { outputFromObservable } from '@angular/core/rxjs-interop';
import { ValidationErrors } from '../../core/api';
import { Player } from '../types';
import { IntControl, NsvFormGroup, TextControl } from '../../core/form/form-group';
import { NsvFormComponent } from '../../core/form/form.component';

export type PlayerData = Omit<DwzPlayer, 'status' | 'gender' | 'yearOfBirth' | 'fideCountry'> & {
  gender: 'W' | 'M' | 'D' | null
  yearOfBirth: number | null
}

type PlayerOption = {name: string, data?: DwzPlayer}

@Component({
  selector: 'player-data',
  standalone: true,
  imports: [ReactiveFormsModule, NgbTypeaheadModule, NsvFormComponent],
  templateUrl: './player-data.component.html',
  styleUrl: './player-data.component.css'
})
export class PlayerDataComponent {
  private subscription

  @Input() lastPlayer: Player | null = null
  @Input() validationErrors: ValidationErrors | undefined = undefined

  // The selected database entry, or the player name in case of manual input.
  selectedPlayer = new FormControl<PlayerOption|null>(null)
  validatePlayerSelection = false  // Replace with opts = {updateOn: 'blur'}?

  club = new FormControl('')
  form = new NsvFormGroup({
    zps: new TextControl('Vereins-Nr.'),
    memberId: new TextControl('Mitglieds-Nr.'),
    yearOfBirth: new IntControl('Geburtsjahr'),
    gender: new TextControl('Geschlecht (M/W/D)'),
    dwz: new IntControl('DWZ'),
    elo: new IntControl('ELO'),
    fideId: new IntControl('FIDE-ID'),
    fideTitle: new TextControl('FIDE-Titel'),
  })

  onPlayerDataChange = outputFromObservable<PlayerData|null>(
    combineLatest([this.selectedPlayer.valueChanges, this.club.valueChanges, this.form.valueChanges])
    .pipe(map(([selectedPlayer, club, formData]) => {
      if (!selectedPlayer || !selectedPlayer.name) return null
      // TODO: simplify by using form.transformedValue (IntControl performs parseInt)
      return {
        name: selectedPlayer.name,
        club: club || '',
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
    this.updateControlStatus()
    this.subscription = this.selectedPlayer.valueChanges.subscribe(player => {
      if (!player) {
        // No player selected.
        this.club.reset()
        this.form.reset()
      } else if (player.data) {
        // Player was selected from the database.
        this.club.setValue(player.data.club)
        this.form.patchValue(player.data as any)
      }
      this.updateControlStatus()
    })
  }

  private updateControlStatus() {
    this.form.hideControls()
    if (this.isManualEntry) {
      this.form.enable()
      this.form.controls.yearOfBirth.visible = true
      this.form.controls.gender.visible = true
    } else {
      this.form.disable()
      this.form.controls.dwz.visible = true
      this.form.controls.elo.visible = true
    }
  }

  get isManualEntry() {
    return this.selectedPlayer.value && !this.selectedPlayer.value.data
  }

  search = (text$: Observable<string>) => {
    return text$.pipe(
      switchMap((term: string) => {
        // Get suggestions based on the term.
        const preferredZps = this.lastPlayer?.playerData?.zps || ''
        const options = term === '' ? of([]) : this.dwz.findPlayer(term, preferredZps)
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
