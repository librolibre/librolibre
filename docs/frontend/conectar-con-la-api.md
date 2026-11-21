# Conectar Angular con la API de Laravel

Guía con código de ejemplo para conectar el front end (Angular) con el back end (Laravel).
El código compila con la versión de Angular del proyecto; los nombres de archivo son una propuesta y se pueden cambiar.

## Cómo funciona

1. **Login:** el botón «Entrar con Google» es un **enlace normal** a Laravel (no una llamada HTTP). Laravel lleva al usuario a Google y, al volver, lo redirige a Angular en `/auth/callback?code=…`.
2. **Token:** Angular cambia ese código de un solo uso (caduca a los 60 s) por un **token** con `POST /auth/token` y lo guarda.
3. **Peticiones:** un **interceptor** añade `Authorization: Bearer <token>` y `Accept: application/json` a todas las llamadas a la API.
4. **Errores:** si el login falla, Laravel redirige a `/?error=cancelled` o `/?error=suspended`. Si el token caduca o se revoca, la API responde `401` y el interceptor cierra la sesión.

Antes de empezar:

- El back end tiene que estar en marcha: `php artisan serve` en `backend/` (API en `http://localhost:8000/api`).
- Todos los endpoints, con sus campos y respuestas, están en `http://localhost:8000/docs/api`.
- El CORS de Laravel ya permite llamadas desde `localhost:4200`.
- Las respuestas vienen dentro de `data` (por ejemplo `{ "data": { "id": 1, … } }`), salvo `POST /auth/token`, que devuelve `token`, `is_new_user` y `user`. Los errores de validación devuelven `422` con `{ "message": …, "errors": { "campo": ["mensaje"] } }`.

## Archivos

```
frontend/src/
├── environments/
│   ├── environment.ts               ← URL de la API en producción
│   └── environment.development.ts   ← URL de la API en local
└── app/
    ├── app.config.ts                ← activa HttpClient y el interceptor
    ├── app.routes.ts                ← rutas /auth/callback y /privacidad
    ├── core/
    │   ├── models.ts                ← tipos de las respuestas
    │   ├── auth.service.ts          ← login, logout y usuario
    │   ├── auth.interceptor.ts      ← añade el token a cada petición
    │   ├── auth.guard.ts            ← protege las páginas que necesitan sesión
    │   └── reference-data.service.ts← ejemplo de servicio normal (idiomas, categorías)
    └── pages/
        ├── home-component/          ← botón de login y mensajes de error
        ├── auth-callback/           ← canjea el código por el token
        └── privacy/                 ← aceptar la política la primera vez
```

## 1. La URL de la API (environments)

Créalos con:

```bash
ng generate environments
```

El comando crea los dos archivos y configura `angular.json` para que `ng serve` use el de desarrollo y `ng build` el de producción. Después, rellénalos:

`frontend/src/environments/environment.development.ts`

```ts
// Development (ng serve): Laravel running with `php artisan serve`.
export const environment = {
  apiUrl: 'http://localhost:8000/api',
};
```

`frontend/src/environments/environment.ts`

```ts
// Production (Vercel): the Laravel API on Heroku. Replace with the real URL when it exists.
export const environment = {
  apiUrl: 'https://librolibre-api.herokuapp.com/api',
};
```

## 2. Activar HttpClient y el interceptor

`frontend/src/app/app.config.ts`

```ts
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { authInterceptor } from './core/auth.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    // HttpClient for the whole app; the interceptor adds the token to every API call
    provideHttpClient(withInterceptors([authInterceptor])),
  ],
};
```

## 3. Tipos de las respuestas

`frontend/src/app/core/models.ts`

```ts
// Shapes of the API responses (see http://localhost:8000/docs/api).

/** The logged-in user's own profile (GET /me, POST /auth/token). */
export interface OwnUser {
  id: number;
  username: string;
  photo_url: string | null;
  description: string | null;
  languages: string[];
  average_rating: number | null;
  ratings_count: number;
  exchanges_count: number;
  member_since: string;
  email: string;
  location: { latitude: number; longitude: number } | null;
  role: 'user' | 'admin';
  privacy_accepted: boolean;
  postal_code: string | null;
}

/** POST /auth/token: the only response that is not wrapped in `data`. */
export interface TokenResponse {
  token: string;
  is_new_user: boolean;
  user: OwnUser;
}

/** Most responses come wrapped in `data`. */
export interface ApiData<T> {
  data: T;
}

export interface Language {
  code: string;
  name: string;
}

export interface Category {
  code: string;
  name: string;
}
```

## 4. El servicio de autenticación

`frontend/src/app/core/auth.service.ts`

```ts
import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, finalize, map, tap } from 'rxjs';
import { environment } from '../../environments/environment';
import { ApiData, OwnUser, TokenResponse } from './models';

const TOKEN_KEY = 'librolibre.token';

/**
 * Login, logout and the logged-in user.
 * The token lives in localStorage so the session survives a page reload.
 */
@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly api = environment.apiUrl;

  /** The Sanctum token, or null when nobody is logged in. */
  readonly token = signal<string | null>(localStorage.getItem(TOKEN_KEY));
  /** The logged-in user's profile, once loaded. */
  readonly user = signal<OwnUser | null>(null);
  readonly isLoggedIn = computed(() => this.token() !== null);

  /** «Entrar con Google» is a normal link to this URL, not an HTTP call. */
  readonly googleLoginUrl = `${this.api}/auth/google/redirect`;

  /** Swaps the one-time code from /auth/callback?code=… for a token. */
  exchangeCode(code: string): Observable<TokenResponse> {
    return this.http.post<TokenResponse>(`${this.api}/auth/token`, { code }).pipe(
      tap((response) => {
        this.saveToken(response.token);
        this.user.set(response.user);
      }),
    );
  }

  /** Reloads the profile (for example after a page reload, when only the token is left). */
  loadMe(): Observable<OwnUser> {
    return this.http.get<ApiData<OwnUser>>(`${this.api}/me`).pipe(
      map((response) => response.data),
      tap((user) => this.user.set(user)),
    );
  }

  acceptPrivacy(): Observable<void> {
    return this.http.post<void>(`${this.api}/me/privacy-consent`, {});
  }

  /** Revokes the token in Laravel; the local session is cleared even if that call fails. */
  logout(): Observable<void> {
    return this.http.post<void>(`${this.api}/auth/logout`, {}).pipe(finalize(() => this.clearSession()));
  }

  clearSession(): void {
    localStorage.removeItem(TOKEN_KEY);
    this.token.set(null);
    this.user.set(null);
  }

  private saveToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
    this.token.set(token);
  }
}
```

## 5. El interceptor

`frontend/src/app/core/auth.interceptor.ts`

```ts
import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { environment } from '../../environments/environment';
import { AuthService } from './auth.service';

/**
 * Runs on every HTTP request: adds the headers Laravel needs, so services never set them by hand.
 * If the API answers 401 (token expired or revoked), the session is cleared and the user goes home.
 */
export const authInterceptor: HttpInterceptorFn = (request, next) => {
  // Only our API gets the token (never send it to Google Maps, Open Library…)
  if (!request.url.startsWith(environment.apiUrl)) {
    return next(request);
  }

  const auth = inject(AuthService);
  const router = inject(Router);
  const token = auth.token();

  // Accept: Laravel answers errors as JSON instead of an HTML page
  const headers: Record<string, string> = { Accept: 'application/json' };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return next(request.clone({ setHeaders: headers })).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 401 && token) {
        auth.clearSession();
        router.navigate(['/']);
      }
      return throwError(() => error);
    }),
  );
};
```

## 6. Proteger páginas (guard)

`frontend/src/app/core/auth.guard.ts`

```ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';

/** For pages that need a session (profile, my books…): without a token, back to home. */
export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  return auth.isLoggedIn() ? true : inject(Router).parseUrl('/');
};
```

## 7. Las rutas

`frontend/src/app/app.routes.ts`

```ts
import { Routes } from '@angular/router';
import { authGuard } from './core/auth.guard';
import { AuthCallback } from './pages/auth-callback/auth-callback';
import { HomeComponent } from './pages/home-component/home-component';
import { Privacy } from './pages/privacy/privacy';

export const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
  },
  {
    // Laravel redirects here after Google: /auth/callback?code=…
    path: 'auth/callback',
    component: AuthCallback,
  },
  {
    path: 'privacidad',
    component: Privacy,
    canActivate: [authGuard],
  },
];
```

## 8. Las páginas

### Home: botón de login y errores

`frontend/src/app/pages/home-component/home-component.ts`

```ts
import { Component, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../core/auth.service';

/** Laravel sends errors back here as /?error=… */
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
  protected readonly loginError = this.errorCode ? (LOGIN_ERRORS[this.errorCode] ?? 'No se ha podido entrar.') : null;

  logout(): void {
    this.auth.logout().subscribe();
  }
}
```

`frontend/src/app/pages/home-component/home-component.html`

```html
@if (loginError) {
  <p>{{ loginError }}</p>
}

@if (auth.isLoggedIn()) {
  <p>Has iniciado sesión.</p>
  <button type="button" (click)="logout()">Cerrar sesión</button>
} @else {
  <!-- A normal link, not HttpClient: the whole browser goes to Laravel and then to Google -->
  <a [href]="auth.googleLoginUrl">Entrar con Google</a>
}
```

### `/auth/callback`: canjear el código

`frontend/src/app/pages/auth-callback/auth-callback.ts`

```ts
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
```

### `/privacidad`: primer acceso

Si `is_new_user` es `true`, el usuario todavía no ha aceptado la política de privacidad.

`frontend/src/app/pages/privacy/privacy.ts`

```ts
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
```

## 9. Un servicio normal (ejemplo)

Cualquier otro servicio sigue el mismo patrón: `HttpClient` + `environment.apiUrl` + quitar el `data`. No hay que poner cabeceras: las añade el interceptor.

`frontend/src/app/core/reference-data.service.ts`

```ts
import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../environments/environment';
import { ApiData, Category, Language } from './models';

/** Example of a normal service: it calls the API and unwraps `data`. The interceptor adds the headers. */
@Injectable({ providedIn: 'root' })
export class ReferenceDataService {
  private readonly http = inject(HttpClient);
  private readonly api = environment.apiUrl;

  getLanguages(): Observable<Language[]> {
    return this.http.get<ApiData<Language[]>>(`${this.api}/languages`).pipe(map((response) => response.data));
  }

  getCategories(): Observable<Category[]> {
    return this.http.get<ApiData<Category[]>>(`${this.api}/categories`).pipe(map((response) => response.data));
  }
}
```

Para usarlo en un componente:

```ts
private readonly referenceData = inject(ReferenceDataService);
protected readonly languages = toSignal(this.referenceData.getLanguages(), { initialValue: [] });
```

(`toSignal` se importa de `@angular/core/rxjs-interop`.)

## Endpoints que ya funcionan de verdad

- `GET /auth/google/redirect`, `GET /auth/google/callback`, `POST /auth/token`, `POST /auth/logout`
- `GET /me`, `PATCH /me`, `DELETE /me`, `POST /me/privacy-consent`, `POST /me/photo`
- `GET /users/{user}`, `GET /languages`, `GET /categories`

El resto todavía son stubs: devuelven datos fijos, pero con la forma correcta, así que ya se puede maquetar con ellos.
