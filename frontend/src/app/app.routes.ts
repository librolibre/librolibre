import { Routes } from '@angular/router';
import { adminGuard } from './core/admin.guard';
import { authGuard } from './core/auth.guard';
import { AdminLayout } from './pages/admin/admin-layout/admin-layout';
import { AdminUsuarios } from './pages/admin/admin-usuarios/admin-usuarios';
import { AuthCallback } from './pages/auth-callback/auth-callback';
import { HomeComponent } from './pages/home-component/home-component';
import { LoginComponent } from './pages/login-component/login-component';
import { PerfilComponent } from './pages/perfil-component/perfil-component';
import { Privacy } from './pages/privacy/privacy';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'privacidad', component: Privacy, canActivate: [authGuard] },
  { path: 'auth/callback', component: AuthCallback },
  {
    path: 'admin',
    component: AdminLayout,
    canActivate: [authGuard, adminGuard],
    children: [
      { path: '', redirectTo: 'usuarios', pathMatch: 'full' },
      { path: 'usuarios', component: AdminUsuarios },
    ],
  },
  {
    path: 'perfil',
    canActivate: [authGuard],
    component: PerfilComponent,
  },
  // Las rutas que no existen vuelven al inicio - Laurence
  { path: '**', redirectTo: '' },
];
