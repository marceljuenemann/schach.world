import { TestBed } from '@angular/core/testing';

import { DwzService } from './dwz.service';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

describe('DwzService', () => {
  let service: DwzService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()]
    });
    service = TestBed.inject(DwzService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
