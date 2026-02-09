import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PlayerDataComponent } from './player-data.component';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';

describe('PlayerDataComponent', () => {
  let component: PlayerDataComponent;
  let fixture: ComponentFixture<PlayerDataComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
      imports: [PlayerDataComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PlayerDataComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
