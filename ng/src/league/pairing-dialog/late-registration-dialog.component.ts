import { Component } from '@angular/core';
import { AbstractControl, FormControl, ValidationErrors } from '@angular/forms';
import { NsvDialog } from '../../core/dialog/dialog';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { IntControl, NsvFormGroup, SelectControl, TextControl } from '../../core/form/form-group';
import { NsvFormComponent } from '../../core/form/form.component';
import { PlayerOption, PlayerSelectComponent } from '../shared/player-select/player-select.component';
import { LateRegistration, PlayerRequest } from '../types';

export interface LateRegistrationDialogParams {
  teamName: string
  teamNumbers: number[]
  preferredZps: string | null
}

function validateYearOfBirth(control: AbstractControl): ValidationErrors | null {
  if (!control.value) return null
  const year = Number(control.value)
  const maxYear = new Date().getFullYear()
  if (Number.isInteger(year) && year >= 1900 && year <= maxYear) return null
  return {message: `Bitte ein Jahr zwischen 1900 und ${maxYear} angeben`}
}

function validateGender(control: AbstractControl): ValidationErrors | null {
  if (!control.value || /^\s*[mwd]\s*$/i.test(control.value)) return null
  return {message: 'Bitte m, w oder d angeben'}
}

/**
 * Collects a late registration for the pairing dialog. Doesn't save anything itself, the
 * result is only stored together with the pairing.
 */
@Component({
    selector: 'league-late-registration-dialog',
    imports: [PlayerSelectComponent, NsvFormComponent, NsvDialogFooterComponent],
    templateUrl: './late-registration-dialog.component.html',
})
export class LateRegistrationDialog extends NsvDialog<LateRegistrationDialogParams, LateRegistration> {
  selectedPlayer = new FormControl<PlayerOption | null>(null)

  teamForm = new NsvFormGroup({
    teamNumber: new SelectControl('Mannschaft', this.params.teamNumbers.map(number => ({
      label: `${number}. Mannschaft`,
      value: String(number),
    }))),
  })

  form = new NsvFormGroup({
    yearOfBirth: new IntControl('Geburtsjahr'),
    gender: new TextControl('Geschlecht (m/w/d)'),
  })

  constructor() {
    super()
    this.form.controls.yearOfBirth.addValidators(validateYearOfBirth)
    this.form.controls.gender.addValidators(validateGender)
    this.teamForm.patchValue({teamNumber: String(this.params.teamNumbers[0] ?? 1)})
  }

  get isManualEntry() {
    return !!this.selectedPlayer.value && !this.selectedPlayer.value.data
  }

  override get isValid() {
    return !!this.selectedPlayer.value && (!this.isManualEntry || this.form.valid)
  }

  override save(): Promise<LateRegistration> {
    return Promise.resolve({
      teamNumber: parseInt(this.teamForm.controls.teamNumber.value),
      player: this.playerRequest(this.selectedPlayer.value!),
    })
  }

  private playerRequest(option: PlayerOption): PlayerRequest {
    const [lastName, ...firstNames] = option.name.split(',')
    const name = {lastName: lastName.trim(), firstName: firstNames.join(',').trim()}
    if (option.data) {
      return {
        ...name,
        title: option.data.fideTitle || undefined,
        zps: `${option.data.zps}-${option.data.memberId}`,
        dwz: option.data.dwz ?? undefined,
        elo: option.data.elo ?? undefined,
        yearOfBirth: option.data.yearOfBirth,
        gender: option.data.gender.toLowerCase() as PlayerRequest['gender'],
      }
    }
    const formValue = this.form.transformedValue
    return {
      ...name,
      yearOfBirth: formValue['yearOfBirth'] ?? undefined,
      gender: (formValue['gender'] || '').toLowerCase() || undefined,
    }
  }
}
