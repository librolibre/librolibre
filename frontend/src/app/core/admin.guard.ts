import {inject} from '@angular/core';
import {CanActivateFn, Router} from '@angular/router';
import {catchError, map, of} from 'rxjs';
import {AuthService} from './auth.service';

export const adminGuard: CanActivateFn = () => {
    const auth= inject(AuthService);
    const home = inject(Router).parseUrl('/');

    const user = auth.user();
    if (user) {
        return user.role === 'admin' ? true : home;
    }

    return auth.loadMe().pipe(
        map((loaded) => loaded.role === 'admin' ? true : home),
        catchError(() => of(home))
    );
};