import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { CatalogBook, Paginated } from './models';

@Injectable({ providedIn: 'root' })
export class CatalogService {
  private readonly http = inject(HttpClient);
  private readonly api = `${environment.apiUrl}/catalog`;

  getBooks(
    params: { page?: number; per_page?: number; category?: string } = {},
  ): Observable<Paginated<CatalogBook>> {
    let httpParams = new HttpParams();

    if (params.page) httpParams = httpParams.set('page', params.page);
    if (params.per_page) httpParams = httpParams.set('per_page', params.per_page);
    if (params.category) httpParams = httpParams.set('category', params.category);

    return this.http.get<Paginated<CatalogBook>>(this.api, { params: httpParams });
  }
}
