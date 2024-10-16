import { inject, InjectionToken } from "@angular/core"
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap"

export const DIALOG_PARAMS = new InjectionToken<any>('NsvDialogParams')

/**
 * Base class for dialog components to help with parameter injection.
 */
export abstract class Dialog<T> {
  public readonly params: T = inject(DIALOG_PARAMS)
  public modal = inject(NgbActiveModal)
}
