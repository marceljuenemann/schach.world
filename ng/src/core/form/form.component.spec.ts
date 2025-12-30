import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { ReactiveFormsModule } from '@angular/forms';

import { NsvFormComponent } from './form.component';
import { NsvFormGroup, TextControl } from './form-group';

describe('NsvFormComponent', () => {
  let component: NsvFormComponent;
  let fixture: ComponentFixture<NsvFormComponent>;
  let testForm: NsvFormGroup;

  beforeEach(async () => {
    // Create a test form with a single text control
    testForm = new NsvFormGroup({
      name: new TextControl('Name', { required: true })
    });

    await TestBed.configureTestingModule({
      imports: [NsvFormComponent, ReactiveFormsModule]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NsvFormComponent);
    component = fixture.componentInstance;

    // Set the test form
    component.form = testForm;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should render a form input for the control', () => {
    const inputElement = fixture.debugElement.query(By.css('#nsv-form-name'));
    expect(inputElement).toBeTruthy();
    expect(inputElement.nativeElement.getAttribute('type')).toBe('text');
  });

  it('should show validation error when control is invalid and touched', () => {
    // Mark the control as touched
    testForm.controls.name.markAsTouched();
    fixture.detectChanges();

    // Since it's required but empty, it should show an error
    const errorElement = fixture.debugElement.query(By.css('.invalid-feedback'));
    expect(errorElement).toBeTruthy();
    expect(errorElement.nativeElement.textContent).toContain('Name darf nicht leer sein');
  });

  it('should properly bind the input value to the form control', () => {
    const inputElement = fixture.debugElement.query(By.css('#nsv-form-name')).nativeElement;

    // Simulate user typing in the input
    inputElement.value = 'Test User';
    inputElement.dispatchEvent(new Event('input'));
    fixture.detectChanges();

    // Check if the form control value was updated
    expect(testForm.controls.name.value).toBe('Test User');
  });
});
