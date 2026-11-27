import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { Router, RouterLink } from '@angular/router';
import { Footer } from '../../components/footer/footer';
import { HeaderComponent } from '../../components/header/header';
import { AuthService } from '../../core/auth.service';
import { OwnUser } from '../../core/models';
import { PerfilService } from '../../core/perfil.service';

@Component({
  imports: [HeaderComponent, Footer, RouterLink, ReactiveFormsModule],
  selector: 'app-perfil-component',
  styleUrl: './perfil-component.css',
  templateUrl: './perfil-component.html',
})
export class PerfilComponent implements OnInit {
  protected readonly auth = inject(AuthService);
  private readonly perfilService = inject(PerfilService);
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly sanitizer = inject(DomSanitizer);

  protected readonly guardando = signal(false);
  protected readonly mensaje = signal<string | null>(null);
  protected readonly avatarPreview = signal<string | null>(null);
  protected readonly ubicacion = signal<{ latitude: number; longitude: number } | null>(null);

  protected readonly mapaUrl = computed<SafeResourceUrl>(() => {
    const loc = this.ubicacion();
    // Si el usuario tiene coordenadas guardadas las centra; si no, centra en Bilbao por defecto
    const lat = loc?.latitude ?? 43.266;
    const lon = loc?.longitude ?? -2.935;
    const d = 0.006;
    const url = `https://www.openstreetmap.org/export/embed.html?bbox=${lon - d}%2C${lat - d}%2C${lon + d}%2C${lat + d}&layer=mapnik&marker=${lat}%2C${lon}`;
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  });

  protected readonly form = this.fb.nonNullable.group({
    username: [''],
    description: [''],
    email: [{ value: '', disabled: true }],
    postal_code: [''],
  });

  ngOnInit(): void {
    const actual = this.auth.user();
    if (actual) {
      this.rellenarFormulario(actual);
    }

    this.perfilService.getMe().subscribe({
      next: (usuario) => this.rellenarFormulario(usuario),
    });
  }

  private rellenarFormulario(usuario: OwnUser): void {
    this.form.patchValue({
      username: usuario.username ?? '',
      description: usuario.description ?? '',
      email: usuario.email ?? '',
      postal_code: usuario.postal_code ?? '',
    });

    this.avatarPreview.set(usuario.photo_url);
    this.ubicacion.set(usuario.location);
  }

  onFotoSeleccionada(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => this.avatarPreview.set(reader.result as string);
    reader.readAsDataURL(file);

    this.perfilService.updatePhoto(file).subscribe({
      next: () => this.mostrarMensaje('Foto actualizada'),
      error: () => this.mostrarMensaje('Error al subir la foto'),
    });
  }

  guardar(): void {
    if (this.guardando()) return;
    this.guardando.set(true);

    const raw = this.form.getRawValue();
    this.perfilService
      .updateMe({
        username: raw.username,
        description: raw.description || null,
        postal_code: raw.postal_code || null,
        location: this.ubicacion(),
      })
      .subscribe({
        next: () => {
          this.guardando.set(false);
          this.mostrarMensaje('Perfil actualizado correctamente');
        },
        error: () => {
          this.guardando.set(false);
          this.mostrarMensaje('No se pudieron guardar los cambios');
        },
      });
  }

  private mostrarMensaje(texto: string): void {
    this.mensaje.set(texto);
    setTimeout(() => this.mensaje.set(null), 3500);
  }

  logout(): void {
    const goHome = () => this.router.navigateByUrl('/');
    this.auth.logout().subscribe({ complete: goHome, error: goHome });
  }
}
