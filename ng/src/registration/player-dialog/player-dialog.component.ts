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
  isManager: boolean,
  player?: Player,
  lastPlayer?: Player | null
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
    group: new FormControl(),
    contactDetails: new NsvFormGroup({
      name: new TextControl('Kontaktperson', {required: true}),
      email: new TextControl('E-Mail-Adresse', {required: true})
    })
  })

  constructor(
    private registrationService: RegistrationService
  ) {
    super()
    if (this.params.player) {
      this.formData.patchValue(this.params.player)
    } else if (this.params.lastPlayer) {
      this.contactDetails.setValue(this.params.lastPlayer.contactDetails)
    }
  }

  isGroupDisabled(groupId: string): boolean {
    if (this.params.isManager) return false
    if (!this.playerData) return true
    const group = this.params.tournament.groups.get(groupId)!
    // TODO: Move into Group class.
    if (group.config.maxDwz && (this.playerData.dwz || 0) > group.config.maxDwz) return true
    if (group.config.minYearOfBirth && (this.playerData.yearOfBirth || Infinity) < group.config.minYearOfBirth) return true
    return false
  }

  onPlayerDataChange(playerData: PlayerData | null) {
    this.playerData = playerData
    if (this.editing) return
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
      ...this.params.player || {},
      ...this.formData.value,
      playerData: this.playerData!
    } as Player
    if (this.editing) {
      await this.registrationService.updatePlayer(this.params.tournament.config.id, player)
    } else {
      await this.registrationService.registerPlayer(this.params.tournament.config.id, player)
    }
    return player
  }

  private get editing() {
    return this.params.player
  }

  private get groupControl() {
    return this.formData.controls.group
  }

  get selectedGroup() {
    return this.groupControl.value
  }

  get contactDetails() {
    return this.formData.controls.contactDetails
  }
}
