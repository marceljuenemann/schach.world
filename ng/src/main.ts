import { createCustomElement } from '@angular/elements';
import { createApplication } from '@angular/platform-browser';
import { RegistrationComponent } from './registration/registration.component';
import { provideHttpClient } from '@angular/common/http';
import { DialogLauncherComponent } from './core/dialog/launcher/launcher.component';
import { MatchDayComponent } from './league/match-day/match-day.component';

// Custom web elements that we define.
// TODO: move to entrypoints.ts
const COMPONENTS = {
  'nsv-dialog-launcher': DialogLauncherComponent,
  'nsv-ng-registration': RegistrationComponent,
  'league-match-day': MatchDayComponent
}

// Create an application.
createApplication({providers: [
  provideHttpClient()
]}).then(app => {
  // Register custom web elements.
  for (let [tag, component] of Object.entries(COMPONENTS)) {
    customElements.define(tag, createCustomElement(component, {injector: app.injector}))
  }
})
