import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

/** First login (is_new_user: true): the user must accept the privacy policy. */
@Component({
  selector: 'app-privacy',
  template: `
    <h1>Política de privacidad</h1>
    <p>(Texto de la política…)</p>
    <button type="button" (click)="accept()">Aceptar</button>
  `,
})
export class Privacy {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  accept(): void {
    this.auth.acceptPrivacy().subscribe(() => this.router.navigateByUrl('/'));
  }
}
