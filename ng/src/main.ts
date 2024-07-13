import { bootstrapApplication } from '@angular/platform-browser';
import { RegistrationComponent } from './registration/registration.component';
import { provideHttpClient } from '@angular/common/http';

// TODO: Better error handler
bootstrapApplication(RegistrationComponent, {
  providers: [
    provideHttpClient()
  ]
})
.catch((err) => console.error(err));
