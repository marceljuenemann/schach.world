import { createCustomElement } from '@angular/elements';
import { createApplication } from '@angular/platform-browser';
import { RegistrationComponent } from './registration/registration.component';
import { provideHttpClient } from '@angular/common/http';
import { DialogLauncherComponent } from './core/dialog/launcher/launcher.component';

// Custom web elements that we define.
// TODO: move to entrypoints.ts
const COMPONENTS = {
  'nsv-dialog-launcher': DialogLauncherComponent,
  'nsv-ng-registration': RegistrationComponent
}

// Create an application.
createApplication({providers: [
  provideHttpClient()
]}).then(app => {
  // Register custom web elements.
  console.log("Test");
  for (let [tag, component] of Object.entries(COMPONENTS)) {
    customElements.define(tag, createCustomElement(component, {injector: app.injector}))
  }
})
