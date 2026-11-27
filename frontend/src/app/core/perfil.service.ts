import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map, tap } from 'rxjs';
import { environment } from '../../environments/environment';
import { AuthService } from './auth.service';
import { ApiData, OwnUser, UpdatePerfilDto } from './models';

@Injectable({ providedIn: 'root' })
export class PerfilService {
  private readonly http = inject(HttpClient);
  private readonly auth = inject(AuthService);
  private readonly api = `${environment.apiUrl}/me`;

  getMe(): Observable<OwnUser> {
    return this.http.get<ApiData<OwnUser>>(this.api).pipe(
      map((res) => res.data),
      tap((user) => this.auth.user.set(user)),
    );
  }

  updateMe(data: UpdatePerfilDto): Observable<OwnUser> {
    return this.http.patch<ApiData<OwnUser>>(this.api, data).pipe(
      map((res) => res.data),
      tap((user) => this.auth.user.set(user)),
    );
  }

  updatePhoto(file: File): Observable<OwnUser> {
    const formData = new FormData();
    formData.append('photo', file);

    return this.http.post<ApiData<OwnUser>>(`${this.api}/photo`, formData).pipe(
      map((res) => res.data),
      tap((user) => this.auth.user.set(user)),
    );
  }

  deleteAccount(): Observable<void> {
    return this.http.delete<void>(this.api);
  }
}
