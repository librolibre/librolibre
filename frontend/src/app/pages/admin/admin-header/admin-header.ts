import { Component, computed, inject, input} from '@angular/core';
import {AuthService} from '../../../core/auth.service';

@Component({
  imports: [],
  selector: 'app-admin-header',
  styleUrl: './admin-header.css',
  templateUrl: './admin-header.html',
})
export class AdminHeader {
  readonly titulo = input.required<string>();
  readonly subtitulo = input('');

  protected readonly auth = inject(AuthService);

  protected readonly iniciales = computed(() => {
    const nombre= this.auth.user()?.username ?? '';
    return(
      nombre
      .split('')
      .filter(Boolean)
      .slice(0, 2)
      .map((parte) => parte[0].toUpperCase())
      .join('') || 'A'
    );
  });
}
