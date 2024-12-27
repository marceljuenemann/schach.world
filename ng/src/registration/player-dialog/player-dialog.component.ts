import { Component } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { Player } from '../types';
import { RegistrationService } from '../registration.service';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvFormGroup, TextControl } from '../../core/form/form-group';
import { NsvDialog } from '../../core/dialog/dialog';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
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
    group: new FormControl<string|null>(null, Validators.required),
    contactDetails: new NsvFormGroup({
      name: new TextControl('Kontaktperson', {required: true}),
      email: new TextControl('E-Mail-Adresse', {required: true})
    }),
    termsAndConditions: new FormControl(false, Validators.requiredTrue)
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
    if (this.params.isManager) {
      this.formData.controls.termsAndConditions.setValue(true)
    }
  }

  onPlayerDataChange(playerData: PlayerData | null) {
    this.playerData = playerData
    if (this.editing || !playerData) return
    if (!this.contactDetails.controls.name.value && playerData.name) {
      this.contactDetails.controls.name.setValue(playerData.name)
    }
    // Possibly unselect current group selection and select last valid group.
    if (!this.selectedGroup || !this.params.tournament.groups.get(this.selectedGroup)?.mayRegister(playerData)) {
      this.formData.controls.group.setValue(null)
      if (playerData.zps) {
        for (let group of Array.from(this.params.tournament.groups.values()).reverse()) {
          if (group.mayRegister(playerData)) {
            this.formData.controls.group.setValue(group.id)
            break
          }
        }
      }
    }
  }

  override get isValid() {
    return !!this.playerData && this.formData.valid
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

  get editing() {
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
