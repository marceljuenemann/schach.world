import { Component, Inject, inject } from '@angular/core';
import { PlayerData, PlayerDataComponent } from '../player-data/player-data.component';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Config, CONFIG_TOKEN } from '../types';
import { RegistrationService } from '../registration.service';
import { firstValueFrom } from 'rxjs';
import { NsvError, processApiError } from '../../core/api';

@Component({
  selector: 'player-dialog',
  standalone: true,
  imports: [PlayerDataComponent],
  templateUrl: './player-dialog.component.html',
  styleUrl: './player-dialog.component.css'
})
export class PlayerDialogComponent {
  modal = inject(NgbActiveModal)

  playerData: PlayerData | null = null
  errors: NsvError | null = null

  constructor(
    @Inject(CONFIG_TOKEN) public config: Config,
    private registrationService: RegistrationService
  ) {}

  isGroupDisabled(groupId: string): boolean {
    if (!this.playerData) return true
    const group = this.config.groups.get(groupId)!
    if (group.maxDwz && (this.playerData.dwz || 0) > group.maxDwz) return true
    if (group.minYearOfBirth && (this.playerData.yearOfBirth || Infinity) < group.minYearOfBirth) return true
    return false
  }

  save() {
    const player = {playerData: this.playerData!}
    firstValueFrom(this.registrationService.registerPlayer('test', player)).then(
      success => this.modal.close(),
      error => this.errors = processApiError(error)
    )
  }
}
