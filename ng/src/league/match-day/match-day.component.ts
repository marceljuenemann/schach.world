import { AfterViewInit, Component, ElementRef, Input, inject } from '@angular/core';
import { DialogService } from '../../core/dialog/dialog.service';
import { PairingDialog } from '../pairing-dialog/pairing-dialog.component';
import { MatchDay } from '../types';

export interface MatchDayComponentParams {
  matchDay: MatchDay
  divisions: unknown[]
  teams: unknown[]
  boardCount: number
}

@Component({
    selector: 'league-match-day',
    imports: [],
    templateUrl: './match-day.component.html',
    styleUrl: './match-day.component.css'
})
export class MatchDayComponent implements AfterViewInit {
  @Input() params: string

  private elementRef = inject(ElementRef)
  private dialogService = inject(DialogService)
  private data: MatchDayComponentParams

  ngAfterViewInit() {
    this.data = JSON.parse(this.params)
    const buttons: NodeListOf<HTMLElement> = this.elementRef.nativeElement.querySelectorAll('[data-nsv-pairing-edit]')
    buttons.forEach(button => button.addEventListener('click', () => this.openPairingDialog(button)))
  }

  private openPairingDialog(button: HTMLElement) {
    const pairingId = parseInt(button.dataset['pairingId']!)
    this.dialogService.open(PairingDialog, { pairingId, ...this.data }, { modalDialogClass: 'pairing-dialog-modal' })
  }
}
