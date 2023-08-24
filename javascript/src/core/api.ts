import { Context } from "./context"

/**
 * Wrapper for all kinds of errors thrown by API calls. The messages are meant
 * to be shown in the UI and meant to be reasonably user-friendly.
 */
export class ApiError {
  private constructor(
    readonly type: 'nsv' | 'http' | 'network' | 'unknown',
    readonly messages: Array<String>
  ) {}

  /**
   * Method for API consumers. This should be used for reject promises, which should
   * always be of type ApiError, but could be arbitrary errors in case of client errors.
   */
  static from(error: any): ApiError {
    if (error instanceof ApiError) return error
    console.error('Unexpected API error', error)
    return new ApiError('unknown', ['Ausnahmefehler: ' + error])
  }

  static async fromResponse(response: Response): Promise<ApiError> {
    // Check if the server returned a well-formed API error.
    if (response.status == 422) {
      const body = await response.json()
      if (body.errorType === 'nsv') {
        return new ApiError(body.errorType, body.errorMessages)
      }
    } else if (response.status == 403) {
      return new ApiError('http', ['Zugriff nicht erlaubt oder abgelaufen. Bitte loggen Sie sich neu ein (HTTP 403)'])
    }
    return new ApiError('http', [`Unerwarteter Fehler (HTTP ${response.status})`])
  }

  static fromNetworkError(error: any): ApiError {
    const msg = `Netzwerkfehler, bitte Internetverbindung überprüfen (${error})`
    return new ApiError('network', [msg])
  }
}

/**
 * Wrapper for API calls.
 */
export class NsvApi {
  constructor(protected context: Context) {}

  protected request<T>(url: string, method: string = 'GET', body: any = null, options: RequestInit = {}): Promise<T> {
    options = {
      ...options,
      method,
      body: body && JSON.stringify(body),
      headers: body ? {
        "Content-Type": "application/json",
      } : undefined
    }
    return this.context.window.fetch(url, options).then(
      async response => {
        if (response.ok) {
          return await response.json()
        } else {
          throw await ApiError.fromResponse(response)
        }
      },
      error => {
        throw ApiError.fromNetworkError(error)
      }
    )
  }
}
