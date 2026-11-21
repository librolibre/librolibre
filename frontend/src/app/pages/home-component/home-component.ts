import { Component, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../core/auth.service';

const LOGIN_ERRORS: Record<string, string> = {
  cancelled: 'Has cancelado el acceso con Google.',
  suspended: 'Tu cuenta está suspendida.',
};

@Component({
  imports: [],
  selector: 'app-home-component',
  styleUrl: './home-component.css',
  templateUrl: './home-component.html',
})
export class HomeComponent {
  protected readonly auth = inject(AuthService);

  private readonly errorCode = inject(ActivatedRoute).snapshot.queryParamMap.get('error');
  protected readonly loginError = this.errorCode
    ? (LOGIN_ERRORS[this.errorCode] ?? 'No se ha podido entrar.')
    : null;

  logout(): void {
    this.auth.logout().subscribe();
  }
}
