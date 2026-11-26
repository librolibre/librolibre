import {HttpClient, HttpParams} from '@angular/common/http';
import {Injectable, inject} from '@angular/core';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {AdminUser, AdminUserFilter, Paginated} from './models';

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
}