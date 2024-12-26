import { inject, InjectionToken } from "@angular/core"
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap"
import { NsvError, processApiError } from "../api"

export const DIALOG_PARAMS = new InjectionToken<any>('NsvDialogParams')

/**
 * Base class for dialog components to help with parameter injection.
 */
export abstract class Dialog<TParams, TResult = void> {
  // Reference to the NgbActiveModal.
  public modal = inject(NgbActiveModal)

  // Parameters passed by the caller.
  public readonly params: TParams = inject(DIALOG_PARAMS)

  // Errors, usually received from the NSV server.
  public errors: NsvError | null = null

  /**
   * Whether the dialog is valid and can be submitted.
   */
  get isValid() {
    return true
  }

  /**
   * Abstract method to be implemented by the dialog component. Failures
   * will automatically be handled and stored in `errors`.
   */
  abstract save(): Promise<TResult>

  saveDialog() {
    if (!this.isValid) return
    this.save().then(
      result => this.modal.close(result),
      error => this.errors = processApiError(error)
    )
  }
}
