import { bootstrapApplication } from '@angular/platform-browser';
import { RegistrationComponent } from './registration/registration.component';

// TODO: Better error handler
bootstrapApplication(RegistrationComponent).catch((err) => console.error(err));
