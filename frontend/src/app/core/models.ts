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
