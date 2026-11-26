import { DatePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { AdminService } from '../../../core/admin.service';
import { AccountStatus, AdminUser, Paginated} from '../../../core/models';
import {AdminHeader} from '../admin-header/admin-header';

@Component({
  imports: [AdminHeader, DatePipe],
  selector: 'app-admin-usuarios',
  styleUrl: './admin-usuarios.css',
  templateUrl: './admin-usuarios.html',
})
export class AdminUsuarios {
  private readonly admin = inject(AdminService);

  protected readonly busqueda = signal('');
  protected readonly estado = signal<AccountStatus | ''>('');
  protected readonly pagina = signal(1);

  protected readonly usuarios = signal<AdminUser[]>([]);
  protected readonly meta = signal<Paginated<AdminUser>['meta'] | null>(null);
  protected readonly cargando = signal(false);
  protected readonly error = signal<string | null>(null);
  protected readonly seleccionado = signal<AdminUser | null>(null);

  private temporizadorBusqueda?: ReturnType<typeof setTimeout>;

  constructor() {
    this.cargar();
  }

  protected cargar(): void{
    this.cargando.set(true);
    this.error.set(null);

    this.admin
    .getUsers({page: this.pagina(),q: this.busqueda(),status: this.estado(),})
    .subscribe({
      next: (respuesta) => {
        this.usuarios.set(respuesta.data);
        this.meta.set(respuesta.meta);
        this.cargando.set(false);
      },
      error: () => {
        this.error.set('Error al cargar los usuarios');
        this.cargando.set(false);
      },
    });
  }


  protected buscar(texto: string): void {
    this.busqueda.set(texto.trim());
    clearTimeout(this.temporizadorBusqueda);
    this.temporizadorBusqueda = setTimeout(() => {
      this.pagina.set(1);
      this.cargar();
    }, 300);
  }

  protected filtrarEstado(valor: string): void {
    this.estado.set(valor as AccountStatus | '');
    this.pagina.set(1);
    this.cargar();
  }

  protected irAPagina(numero: number): void {
    this.pagina.set(numero);
    this.cargar();
  }

  protected seleccionar(usuario: AdminUser): void {
    this.seleccionado.set(usuario);
  }

  /** "Marta Aguirre" → "MA" */
  protected iniciales(nombre: string): string {
    return (
      nombre
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte[0].toUpperCase())
        .join('') || '?'
    );
  }

  /** 4.7 → "4,7"; no ratings yet → "—" */
  protected valoracion(media: number | null): string {
    return media ? media.toFixed(1).replace('.', ',') : '—';
  }
}
