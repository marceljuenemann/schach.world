import { HttpErrorResponse } from "@angular/common/http"

export interface NsvError {
  errorType: 'nsv' | 'http' | 'network' | 'client'
  errorMessages: Array<string>
  validationErrors?: ValidationErrors
}

export type ValidationErrors = Record<string, Array<{message: string}>>

export function processApiError(error: any): NsvError {
  if (!(error instanceof HttpErrorResponse)) {
    return {
      errorType: 'client',
      errorMessages: ['Ausnahmefehler: ' + error]
    }
  }
  if (!error.status) {
    return {
      errorType: 'network',
      errorMessages: ['Netzwerkfehler, bitte Internetverbindung überprüfen']
    }
  }
  if (error.status == 422 && error.error?.errorType == 'nsv') {
    return error.error
  }
  if (error.status == 403) {
    return {
      errorType: 'http',
      errorMessages: ['Zugriff nicht erlaubt oder abgelaufen. Bitte loggen Sie sich neu ein (HTTP 403)']
    }
  }
  return {
    errorType: 'http',
    errorMessages: [`Unerwarteter Fehler (HTTP ${error.status})`]
  }
}
