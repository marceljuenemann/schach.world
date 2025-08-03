import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogLauncherComponent } from './launcher.component';

describe('DialogLauncherComponent', () => {
  let component: DialogLauncherComponent;
  let fixture: ComponentFixture<DialogLauncherComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DialogLauncherComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DialogLauncherComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
