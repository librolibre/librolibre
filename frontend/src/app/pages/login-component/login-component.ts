import { Component, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-login-component',
  imports: [RouterLink],
  styleUrl: './login-component.css',
  templateUrl: './login-component.html',
})
export class LoginComponent {
  protected readonly auth = inject(AuthService);
}
