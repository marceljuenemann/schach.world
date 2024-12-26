import { Component } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { Player } from '../types';
import { RegistrationService } from '../registration.service';
import { firstValueFrom } from 'rxjs';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvFormGroup, TextControl } from '../../core/form/form-group';
import { NsvDialog } from '../../core/dialog/dialog';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Tournament } from '../tournament';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';

export interface PlayerDialogParams {
  tournament: Tournament,
  lastPlayer: Player | null
}

@Component({
  selector: 'player-dialog',
  standalone: true,
  imports: [PlayerDataComponent, NsvFormComponent, ReactiveFormsModule, NsvDialogFooterComponent],
  templateUrl: './player-dialog.component.html',
  styleUrl: './player-dialog.component.css'
})
export class PlayerDialogComponent extends NsvDialog<PlayerDialogParams, Player> {
  playerData: PlayerData | null = null

  formData = new FormGroup({
    group: new FormControl()
  })

  // ToDo: move into formData.
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
    const group = this.params.tournament.groups.get(groupId)!
    // TODO: Move into Group class.
    if (group.config.maxDwz && (this.playerData.dwz || 0) > group.config.maxDwz) return true
    if (group.config.minYearOfBirth && (this.playerData.yearOfBirth || Infinity) < group.config.minYearOfBirth) return true
    return false
  }

  get selectedGroup() {
    return this.formData.controls.group.value
  }

  onPlayerDataChange(playerData: PlayerData | null) {
    this.playerData = playerData
    if (!this.contactDetails.controls.name.value && playerData && playerData.name) {
      this.contactDetails.controls.name.setValue(playerData.name)
    }
    // Unselect current group selection and select last valid group.
    if (playerData && (!this.selectedGroup || this.isGroupDisabled(this.selectedGroup))) {
      this.formData.controls.group.setValue(null)
      if (playerData?.dwz && playerData.yearOfBirth) {
        for (let groupId of Array.from(this.params.tournament.groups.keys()).reverse()) {
          if (!this.isGroupDisabled(groupId)) {
            this.formData.controls.group.setValue(groupId)
            break
          }
        }
      }
    }
  }

  override get isValid() {
    return this.playerData && this.selectedGroup && this.contactDetails.valid
  }

  override async save(): Promise<Player> {
    const player = {
      playerData: this.playerData!,
      group: this.formData.controls.group.value,
      contactDetails: this.contactDetails.value
    } as Player
    await firstValueFrom(this.registrationService.registerPlayer('test', player))
    return player
  }
}
