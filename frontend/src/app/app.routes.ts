import { Routes } from '@angular/router';
import { authGuard } from './core/auth.guard';
import { AuthCallback } from './pages/auth-callback/auth-callback';
import { HomeComponent } from './pages/home-component/home-component';
import { LoginComponent } from './pages/login-component/login-component';
import { Privacy } from './pages/privacy/privacy';

export const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
  },
  {
    path: 'auth/callback',
    component: LoginComponent,
  },
  {
    path: 'privacidad',
    component: Privacy,
    canActivate: [authGuard],
  },
  {
    // Laravel redirects here after Google: /auth/callback?code=…
    path: 'auth/callback',
    component: AuthCallback,
  },
];
