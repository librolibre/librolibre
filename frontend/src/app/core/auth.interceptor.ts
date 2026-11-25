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
