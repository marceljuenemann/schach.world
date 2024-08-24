import { Component, Inject, inject } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { Config, Player } from '../types';
import { RegistrationService } from '../registration.service';
import { firstValueFrom } from 'rxjs';
import { NsvError, processApiError } from '../../core/api';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvFormGroup, TextControl } from '../../core/form/form-group';
import { JsonPipe } from '@angular/common';
import { Dialog } from '../../core/dialog';

export interface PlayerDialogParams {
  config: Config,
  lastPlayer: Player | null
}

@Component({
  selector: 'player-dialog',
  standalone: true,
  imports: [PlayerDataComponent, NsvFormComponent, JsonPipe],
  templateUrl: './player-dialog.component.html',
  styleUrl: './player-dialog.component.css'
})
export class PlayerDialogComponent extends Dialog<PlayerDialogParams> {
  playerData: PlayerData | null = null
  errors: NsvError | null = null

  contactDetails = new NsvFormGroup({
    name: new TextControl('Kontaktperson', {required: true}),
    email: new TextControl('E-Mail-Adresse', {required: true})
  })

  constructor(
    private registrationService: RegistrationService
  ) {
    super()
    if (this.params.lastPlayer) {
      this.contactDetails.setValue(this.params.lastPlayer.contactDetails)
    }
  }

  isGroupDisabled(groupId: string): boolean {
    if (!this.playerData) return true
    const group = this.params.config.groups.get(groupId)!
    if (group.maxDwz && (this.playerData.dwz || 0) > group.maxDwz) return true
    if (group.minYearOfBirth && (this.playerData.yearOfBirth || Infinity) < group.minYearOfBirth) return true
    return false
  }

  get isValid() {
    return this.playerData && this.contactDetails.valid
  }

  save() {
    const player = {
      playerData: this.playerData!,
      contactDetails: this.contactDetails.value
    } as Player
    firstValueFrom(this.registrationService.registerPlayer('test', player)).then(
      success => this.modal.close(player),
      error => this.errors = processApiError(error)
    )
  }
}
