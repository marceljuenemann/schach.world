import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RegistrationComponent } from './registration.component';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';

import * as testConfig from './testing/test-config.json';

describe('RegistrationComponent', () => {
  let component: RegistrationComponent;
  let fixture: ComponentFixture<RegistrationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
      imports: [RegistrationComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RegistrationComponent);
    component = fixture.componentInstance;
    component.configString = JSON.stringify(testConfig);
    component.playersString = JSON.stringify([]);
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  describe('mayOpenRegistration', () => {
    function setup(configOverrides: object, isManager = false): RegistrationComponent {
      const f = TestBed.createComponent(RegistrationComponent);
      const c = f.componentInstance;
      c.configString = JSON.stringify({...testConfig, ...configOverrides});
      c.playersString = JSON.stringify([]);
      c.isManager = isManager;
      f.detectChanges();
      return c;
    }

    it('is true when deadline has not passed', () => {
      expect(setup({deadline: '2099-12-31'}).mayOpenRegistration).toBe(true);
    });

    it('is false when deadline has passed', () => {
      expect(setup({deadline: '2000-01-01'}).mayOpenRegistration).toBe(false);
    });

    it('is true for a manager even when deadline has passed', () => {
      expect(setup({deadline: '2000-01-01'}, true).mayOpenRegistration).toBe(true);
    });

    it('is false when registrationStart is in the future', () => {
      expect(setup({registrationStart: '2099-12-31'}).mayOpenRegistration).toBe(false);
    });

    it('is true when registrationStart is in the past', () => {
      expect(setup({registrationStart: '2000-01-01'}).mayOpenRegistration).toBe(true);
    });
  });
});
