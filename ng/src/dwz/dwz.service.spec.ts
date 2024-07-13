import { TestBed } from '@angular/core/testing';

import { DwzService } from './dwz.service';

describe('DwzService', () => {
  let service: DwzService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DwzService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
