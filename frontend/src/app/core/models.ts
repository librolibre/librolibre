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

export interface Paginated<T> {
  data: T[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
  };
}

export type AccountStatus = 'active' | 'suspended' | 'deleted';

export interface AdminUser {
  id: number;
  username: string;
  photo_url: string | null;
  email: string;
  status: AccountStatus;
  role: 'user' | 'admin';
  created_at: string;
  books_count: number;
  average_rating: number | null;
  strikes: number;
}

export interface UpdatePerfilDto {
  username?: string;
  description?: string | null;
  postal_code?: string | null;
  location?: { latitude: number; longitude: number } | null;
}

export interface AdminUserFilter {
  page?: number;
  q?: string;
  status?: AccountStatus | '';
}
