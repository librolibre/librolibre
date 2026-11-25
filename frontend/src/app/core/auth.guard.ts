import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { catchError, map, of } from 'rxjs';
import { AuthService } from './auth.service';
import { OwnUser } from './models';

// /** For pages that need a session (profile, my books…): without a token, back to home. */
// export const authGuard: CanActivateFn = () => {
//   const auth = inject(AuthService);
//   return auth.isLoggedIn() ? true : inject(Router).parseUrl('/');
// };

// Sin sesión, a /login; sin aceptar la política, a /privacidad (#24) - Laurence
export const authGuard: CanActivateFn = (_route, state) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (!auth.isLoggedIn()) {
    return router.parseUrl('/login');
  }

  const check = (user: OwnUser) =>
    user.privacy_accepted || state.url === '/privacidad' ? true : router.parseUrl('/privacidad');

  const user = auth.user();
  return user
    ? check(user)
    : auth.loadMe().pipe(
        map(check),
        catchError(() => of(router.parseUrl('/login'))),
      );
};
