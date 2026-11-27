import { ComponentFixture, TestBed } from '@angular/core/testing';
import { AdminAvatar } from './admin-avatar';

describe('AdminAvatar', () => {
  let component: AdminAvatar;
  let fixture: ComponentFixture<AdminAvatar>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminAvatar],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminAvatar);
    component = fixture.componentInstance;
    fixture.componentRef.setInput('nombre', 'Marta Aguirre');
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
