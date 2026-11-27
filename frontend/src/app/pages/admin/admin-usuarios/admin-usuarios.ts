import { DatePipe } from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {AdminAvatar} from '../admin-avatar/admin-avatar';
import { Component, inject, signal } from '@angular/core';
import { AdminService } from '../../../core/admin.service';
import { AccountStatus, AdminUser, Paginated} from '../../../core/models';
import {AdminHeader} from '../admin-header/admin-header';
import {ModalMotivo} from '../modal-motivo/modal-motivo';
import {formatearValoracion} from '../admin-formato';
import { UsuarioDetalle } from '../usuario-detalle/usuario-detalle';

@Component({
  imports: [AdminAvatar, AdminHeader, ModalMotivo,UsuarioDetalle, DatePipe],
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

  protected readonly accion = signal<'suspender' | 'reactivar' | null>(null);
  protected readonly enviando = signal(false);
  protected readonly errorAccion = signal<string | null>(null);

  protected readonly valoracion = formatearValoracion;

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

        const actual = this.seleccionado();
        if (actual) {
          this.seleccionado.set(respuesta.data.find((u) => u.id === actual.id) ?? null);
        }
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

  protected cerrarDetalle(): void {
    this.seleccionado.set(null);
  }

   protected confirmarAccion(motivo: string): void {
    const usuario = this.seleccionado();
    const accion = this.accion();
    if (!usuario || !accion) {
      return;
    }

    this.enviando.set(true);
    this.errorAccion.set(null);

    const peticion =
      accion === 'suspender'
        ? this.admin.suspendUser(usuario.id, motivo)
        : this.admin.reactivateUser(usuario.id, motivo);

    peticion.subscribe({
      next: () => {
        this.enviando.set(false);
        this.accion.set(null);
        this.cargar();
      },
      error: (err: HttpErrorResponse) => {
        this.enviando.set(false);
        this.errorAccion.set(err.error?.message ?? 'No se ha podido completar la acción.');
      },
    });
  }

  protected cancelarAccion(): void {
    this.accion.set(null);
    this.errorAccion.set(null);
  }
}
