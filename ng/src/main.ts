import { createCustomElement } from '@angular/elements';
import { createApplication } from '@angular/platform-browser';
import { RegistrationComponent } from './registration/registration.component';
import { provideHttpClient } from '@angular/common/http';

// Custom web elements that we define.
const COMPONENTS = {
  'nsv-ng-registration': RegistrationComponent
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
