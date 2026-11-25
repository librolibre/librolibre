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

  // Marca la política como aceptada en el usuario cargado - Laurence
  acceptPrivacy(): Observable<void> {
    // return this.http.post<void>(`${this.api}/me/privacy-consent`, {});
    return this.http
      .post<void>(`${this.api}/me/privacy-consent`, {})
      .pipe(tap(() => this.user.update((user) => (user ? { ...user, privacy_accepted: true } : user))));
  }

  /** Revokes the token in Laravel; the local session is cleared even if that call fails. */
  logout(): Observable<void> {
    return this.http
      .post<void>(`${this.api}/auth/logout`, {})
      .pipe(finalize(() => this.clearSession()));
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
