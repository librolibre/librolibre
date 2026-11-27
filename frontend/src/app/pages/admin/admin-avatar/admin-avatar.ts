import { Component, computed, input, signal } from '@angular/core';

@Component({
  imports: [],
  selector: 'app-admin-avatar',
  styleUrl: './admin-avatar.css',
  templateUrl: './admin-avatar.html',
})
export class AdminAvatar {
  readonly nombre = input.required<string>();
  readonly foto = input<string | null>(null);

  protected readonly fotoRota = signal<string | null>(null);

  protected readonly mostrarFoto = computed(() => !!this.foto() && this.foto() !== this.fotoRota());

  protected readonly iniciales = computed(
    () =>
      this.nombre()
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte[0].toUpperCase())
        .join('') || '?',
  );
}
