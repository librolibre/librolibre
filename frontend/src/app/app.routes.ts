import { Routes } from '@angular/router';
import { authGuard } from './core/auth.guard';
import { adminGuard } from './core/admin.guard';
import { AuthCallback } from './pages/auth-callback/auth-callback';
import { HomeComponent } from './pages/home-component/home-component';
import { LoginComponent } from './pages/login-component/login-component';
import {AdminLayout} from './pages/admin/admin-layout/admin-layout';
import {AdminUsuarios} from './pages/admin/admin-usuarios/admin-usuarios';
import { Privacy } from './pages/privacy/privacy';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'privacidad', component: Privacy, canActivate: [authGuard] },
  { path: 'auth/callback', component: AuthCallback },
  {path: 'admin', component: AdminLayout, canActivate: [authGuard, adminGuard], 
  children: [
    { path: '', redirectTo: 'usuarios', pathMatch: 'full' },
    { path: 'usuarios', component: AdminUsuarios },
  ],
  },
  // Las rutas que no existen vuelven al inicio - Laurence
  { path: '**', redirectTo: '' },
];
