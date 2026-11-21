import { Component } from '@angular/core';
import { ApiServices } from '../../services/login.service';

@Component({
  imports: [],
  selector: 'app-login-component',
  styleUrl: './login-component.css',
  templateUrl: './login-component.html',
})
export class LoginComponent {
  login() {
    // Aquí puedes agregar la lógica de inicio de sesión, como llamar a un servicio de autenticación.
    console.log('Iniciando sesión...');
  }
}
