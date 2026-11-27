import {HttpClient, HttpParams} from '@angular/common/http';
import {Injectable, inject} from '@angular/core';
import {Observable, map} from 'rxjs';
import {environment} from '../../environments/environment';
import {ApiData, AdminUser, AdminUserDetail, AdminUserFilter, Paginated} from './models';

@Injectable({providedIn: 'root'})
export class AdminService {
    private readonly http = inject(HttpClient);
    private readonly api = `${environment.apiUrl}/admin`;

    getUsers(filter: AdminUserFilter = {}): Observable<Paginated<AdminUser>> {
        let params = new HttpParams();
        if (filter.page) {
            params = params.set('page', filter.page);
        }

        if (filter.q) {
            params = params.set('q', filter.q);
        }

        if(filter.status) {
            params = params.set('status', filter.status);
        }

        return this.http.get<Paginated<AdminUser>>(`${this.api}/users`, {params});
    }

    getUser(id: number): Observable<AdminUserDetail> {
        return this.http
        .get<ApiData<AdminUserDetail>>(`${this.api}/users/${id}`)
        .pipe(map((response) => response.data));
    }

    suspendUser(id: number, reason: string): Observable<unknown> {
        return this.http.post(`${this.api}/users/${id}/suspend`, {reason});
    }

    reactivateUser(id: number, reason: string): Observable<unknown> {
        return this.http.post(`${this.api}/users/${id}/reactivate`, {reason});
    }
}