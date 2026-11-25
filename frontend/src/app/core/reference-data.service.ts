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
    return this.http
      .get<ApiData<Language[]>>(`${this.api}/languages`)
      .pipe(map((response) => response.data));
  }

  getCategories(): Observable<Category[]> {
    return this.http
      .get<ApiData<Category[]>>(`${this.api}/categories`)
      .pipe(map((response) => response.data));
  }
}
