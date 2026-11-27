import { Component, OnInit, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  imports: [RouterLink],
  selector: 'app-header',
  styleUrl: './header.css',
  templateUrl: './header.html',
})
export class HeaderComponent implements OnInit {
  protected readonly auth = inject(AuthService);
  private readonly router = inject(Router);
  protected readonly menuAbierto = signal(false);
  protected readonly fotoRota = signal(false);

  ngOnInit(): void {
    if (this.auth.isLoggedIn() && !this.auth.user()) {
      this.auth.loadMe().subscribe({ error: () => {} });
    }
  }

  toggleMenu(): void {
    this.menuAbierto.update((abierto) => !abierto);
  }

  logout(): void {
    this.menuAbierto.set(false);
    const goHome = () => this.router.navigateByUrl('/');
    this.auth.logout().subscribe({ complete: goHome, error: goHome });
  }
}
