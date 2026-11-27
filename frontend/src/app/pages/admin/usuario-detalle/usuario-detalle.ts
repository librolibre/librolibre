import { DatePipe } from '@angular/common';
import { Component, effect, inject, input, output, signal } from '@angular/core';
import { AdminService } from '../../../core/admin.service';
import { AdminUser, AdminUserDetail } from '../../../core/models';
import { AdminAvatar } from '../admin-avatar/admin-avatar';
import { estrellas, formatearValoracion } from '../admin-formato';

@Component({
  imports: [DatePipe, AdminAvatar],
  selector: 'app-usuario-detalle',
  styleUrl: './usuario-detalle.css',
  templateUrl: './usuario-detalle.html',
})
export class UsuarioDetalle {
   private readonly admin = inject(AdminService);

  readonly usuario = input.required<AdminUser>();
  readonly cerrar = output<void>();
  readonly accion = output<'suspender' | 'reactivar'>();

  protected readonly detalle = signal<AdminUserDetail | null>(null);
  protected readonly cargando = signal(false);
  protected readonly error = signal(false);
  private readonly portadasRotas = signal(new Set<string>());

  protected readonly valoracion = formatearValoracion;
  protected readonly estrellas = estrellas;

  constructor() {
    effect((onCleanup) => {
      const id = this.usuario().id;
      this.detalle.set(null);
      this.error.set(false);
      this.cargando.set(true);

      const peticion = this.admin.getUser(id).subscribe({
        next: (detalle) => {
          this.detalle.set(detalle);
          this.cargando.set(false);
        },
        error: () => {
          this.error.set(true);
          this.cargando.set(false);
        },
      });

      onCleanup(() => peticion.unsubscribe());
    });
  }

  protected portadaVisible(url: string | null): boolean {
    return !!url && !this.portadasRotas().has(url);
  }

  protected marcarPortadaRota(url: string | null): void {
    if (url) {
      this.portadasRotas.update((rotas) => new Set(rotas).add(url));
    }
  }

  protected estadoLibro(estado: string): string {
    const nombres: Record<string, string> = {
      available: 'Disponible',
      paused: 'Pausado',
      hidden: 'Oculto',
    };
    return nombres[estado] ?? estado;
  }
}
