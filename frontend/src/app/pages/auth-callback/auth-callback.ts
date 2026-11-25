import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';

/**
 * Route /auth/callback: Laravel sends the browser here after Google, with ?code=…
 * Swaps the code for the token, then leaves the page so the code disappears from the URL.
 */
@Component({
  selector: 'app-auth-callback',
  imports: [RouterLink],
  template: `
    @if (error()) {
      <p>{{ error() }}</p>
      <a routerLink="/">Volver al inicio</a>
    } @else {
      <p>Entrando…</p>
    }
  `,
})
export class AuthCallback implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly auth = inject(AuthService);

  protected readonly error = signal<string | null>(null);

  ngOnInit(): void {
    const code = this.route.snapshot.queryParamMap.get('code');
    if (!code) {
      this.error.set('Falta el código de acceso.');
      return;
    }

    // The code works only once and expires after 60 seconds
    this.auth.exchangeCode(code).subscribe({
      next: (response) => {
        // replaceUrl: the address with ?code=… is not kept in the browser history
        const target = response.is_new_user ? '/privacidad' : '/';
        this.router.navigateByUrl(target, { replaceUrl: true });
      },
      error: () => this.error.set('El código no es válido o ha caducado. Vuelve a intentarlo.'),
    });
  }
}
