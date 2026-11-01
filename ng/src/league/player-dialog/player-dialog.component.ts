import { Component } from '@angular/core';
import { FormControl } from '@angular/forms';
import { IntControl, NsvFormGroup, SelectControl, TextControl } from '../../core/form/form-group';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { CreateOrUpdatePlayerData, LeagueService } from '../league.service';
import { PlayerOption, PlayerSelectComponent } from '../shared/player-select/player-select.component';

export interface PlayerDialogParams {
  teamId: number
  roundCount: number
  preferredZps: string | null
  currentRound: number | null
  player?: {
    id: number
    firstName: string
    lastName: string
    title: string
    yearOfBirth: number | null
    gender: string
    zps: string | null
    dwz: number | null
    elo: number | null
    lateRegistrationRound: number | null
  }
}

@Component({
    selector: 'league-player-dialog',
    imports: [PlayerSelectComponent, NsvFormComponent, NsvDialogFooterComponent],
    templateUrl: './player-dialog.component.html',
    styleUrl: './player-dialog.component.css'
})
export class PlayerDialog extends NsvDialog<PlayerDialogParams> {
  selectedPlayer = new FormControl<PlayerOption | null>(null)

  form = new NsvFormGroup({
    title: new TextControl('Titel'),
    zps: new TextControl('ZPS'),
    yearOfBirth: new IntControl('Geburtsjahr'),
    gender: new TextControl('Geschlecht (m/w/d)'),
    dwz: new IntControl('DWZ'),
    elo: new IntControl('ELO'),
  })

  registrationForm = new NsvFormGroup({
    lateRegistrationRound: new SelectControl('Nachmeldung', [
      {label: 'Regulärer Spieler', value: ''},
      ...Array.from({length: this.params.roundCount}, (_, i) => ({
        label: `Nachmeldung ${i + 1}. Spieltag`,
        value: String(i + 1),
      })),
    ]),
  })

  // Form values from the dialog params.
  private initialValues: Record<string, any>

  constructor(private leagueService: LeagueService) {
    super()
    this.selectedPlayer.valueChanges.subscribe(player => {
      if (player?.data) {
        this.form.patchValue({
          title: player.data.fideTitle || '',
          yearOfBirth: player.data.yearOfBirth,
          gender: player.data.gender,
          zps: `${player.data.zps}-${player.data.memberId}`,
          dwz: player.data.dwz,
          elo: player.data.elo,
        })
      } else if (player) {
        this.clearChangedFields()
      }
    })

    if (this.params.player) {
      const player = this.params.player
      this.selectedPlayer.setValue({name: `${player.lastName}, ${player.firstName}`})
      this.form.patchValue(player)
      this.registrationForm.patchValue({lateRegistrationRound: player.lateRegistrationRound ? String(player.lateRegistrationRound) : ''})
    } else if (this.params.currentRound) {
      this.registrationForm.patchValue({lateRegistrationRound: String(this.params.currentRound)})
    }
    this.initialValues = this.form.getRawValue()
  }

  /**
   * Clears all fields except the ones that still match the dialog params.
   */
  private clearChangedFields() {
    for (const [key, control] of Object.entries(this.form.controls)) {
      if (control.value !== this.initialValues[key]) {
        control.setValue('')
      }
    }
  }

  get editing() {
    return !!this.params.player
  }

  override get isValid() {
    return !!this.selectedPlayer.value && this.form.valid && this.registrationForm.valid
  }

  override save(): Promise<void> {
    const name = this.selectedPlayer.value?.name || ''
    const commaIndex = name.indexOf(',')
    const lastName = (commaIndex >= 0 ? name.slice(0, commaIndex) : name).trim()
    const firstName = (commaIndex >= 0 ? name.slice(commaIndex + 1) : '').trim()

    const round = this.registrationForm.controls.lateRegistrationRound.value
    const formValue = this.form.transformedValue

    const data: CreateOrUpdatePlayerData = {
      firstName,
      lastName,
      title: formValue['title'],
      zps: formValue['zps'],
      dwz: formValue['dwz'],
      elo: formValue['elo'],
      yearOfBirth: formValue['yearOfBirth'],
      gender: (formValue['gender'] || '').toLowerCase(),
      lateRegistrationRound: round ? parseInt(round) : null,
    }

    return this.editing
      ? this.leagueService.updatePlayer(this.params.teamId, this.params.player!.id, data)
      : this.leagueService.createPlayer(this.params.teamId, data)
  }
}
