import { ComponentFixture, TestBed } from '@angular/core/testing';
import { UsuarioDetalle } from './usuario-detalle';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { AdminUser } from '../../../core/models';

const usuario: AdminUser = {
  id: 1,
  username: 'Marta Aguirre',
  photo_url: null,
  email: 'marta@example.com',
  status: 'active',
  role: 'user',
  created_at: '2026-10-14T10:00:00Z',
  books_count: 0,
  average_rating: null,
  strikes: 0,
};

describe('UsuarioDetalle', () => {
  let component: UsuarioDetalle;
  let fixture: ComponentFixture<UsuarioDetalle>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UsuarioDetalle],
      providers: [provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(UsuarioDetalle);
    component = fixture.componentInstance;
    fixture.componentRef.setInput('usuario', usuario);
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
