import { Component } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { Player } from '../types';
import { RegistrationService } from '../registration.service';
import { NsvFormComponent } from '../../core/form/form.component';
import { NsvFormGroup, TextControl } from '../../core/form/form-group';
import { NsvDialog } from '../../core/dialog/dialog';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Group, Tournament } from '../tournament';
import { NsvDialogFooterComponent } from '../../core/dialog/footer/dialog-footer.component';
import { JsonPipe } from '@angular/common';

export interface PlayerDialogParams {
  tournament: Tournament,
  isManager: boolean,
  player?: Player,
  lastPlayer?: Player | null
}

@Component({
    selector: 'player-dialog',
    imports: [PlayerDataComponent, NsvFormComponent, ReactiveFormsModule, NsvDialogFooterComponent, JsonPipe],
    templateUrl: './player-dialog.component.html',
    styleUrl: './player-dialog.component.css'
})
export class PlayerDialogComponent extends NsvDialog<PlayerDialogParams, Player> {
  playerData: PlayerData | null = null
  isDuplicatePlayer: boolean = false

  formData = new FormGroup({
    group: new FormControl<string|null>(null, Validators.required),
    additionalFields: new NsvFormGroup({}),
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
    this.additionalFields.addControls(this.params.tournament.config.additionalFields || [])
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
    this.isDuplicatePlayer = !!playerData && !this.editing && this.params.tournament.hasPlayer(playerData)
    if (this.editing || !playerData || this.isDuplicatePlayer) return

    if (!this.contactDetails.controls.name.value && playerData.name) {
      this.contactDetails.controls.name.setValue(playerData.name)
    }
    // Possibly unselect current group selection and select last valid group.
    if (!this.selectedGroup || this.registrationRestriction(this.params.tournament.groups.get(this.selectedGroup)!, playerData)) {
      this.formData.controls.group.setValue(null)
      if (playerData.zps) {
        for (let group of Array.from(this.params.tournament.groups.values()).reverse()) {
          if (!group.config.hidden && !this.registrationRestriction(group, playerData)) {
            this.formData.controls.group.setValue(group.id)
            break
          }
        }
      }
    }
  }

  /**
   * Return the reason why a player may not register for the given group, if any.
   */
  registrationRestriction(group: Group, playerData: PlayerData): string | null {
    if (group.config.minYearOfBirth && (playerData.yearOfBirth || 0) < group.config.minYearOfBirth) {
      return `bis Jahrgang ${group.config.minYearOfBirth}`
    }
    if (group.config.minDwz && (playerData.dwz || 0) < group.config.minDwz) {
      return `ab DWZ ${group.config.minDwz}`
    }
    if (group.config.maxDwz && (playerData.dwz || 0) > group.config.maxDwz) {
      return `bis DWZ ${group.config.maxDwz}`
    }
    if (group.config.requireFideId && !playerData.fideId) {
      return `FIDE-ID erforderlich`
    }
    return null
  }

  isWaitlistGroupSelected(): boolean {
    if (this.editing || !this.selectedGroup) return false
    const group = this.params.tournament.groups.get(this.selectedGroup)
    return group ? !group.availableSlots : false
  }

  override get isValid() {
    return !!this.playerData && !this.isDuplicatePlayer && this.formData.valid
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
      if (this.isWaitlistGroupSelected()) {
        player.waitlist = true
      }
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

  get additionalFields() {
    return this.formData.controls.additionalFields
  }

  get contactDetails() {
    return this.formData.controls.contactDetails
  }
}
