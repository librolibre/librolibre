import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ModalMotivo } from './modal-motivo';

describe('ModalMotivo', () => {
  let component: ModalMotivo;
  let fixture: ComponentFixture<ModalMotivo>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ModalMotivo],
    }).compileComponents();

    fixture = TestBed.createComponent(ModalMotivo);
    component = fixture.componentInstance;
    fixture.componentRef.setInput('titulo', '¿Suspender a Marta?');
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
