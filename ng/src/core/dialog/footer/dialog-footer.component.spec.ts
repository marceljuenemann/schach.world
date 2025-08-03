import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NsvDialogFooterComponent } from './dialog-footer.component';

describe('NsvDialogFooterComponent', () => {
  let component: NsvDialogFooterComponent;
  let fixture: ComponentFixture<NsvDialogFooterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NsvDialogFooterComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NsvDialogFooterComponent);
    component = fixture.componentInstance;
    component.dialog = { erorrs: [] } as any; // TODO: use Spy or real Dialog instance
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
