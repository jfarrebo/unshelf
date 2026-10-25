export type UserRole = 'user' | 'admin';

/** Dades de l'usuari tal com les retorna l'API (UserResource de Laravel). */
export interface User {
  id_user: number;
  name: string;
  email: string;
  city: string;
  postal_code: string | null;
  avatar_url: string | null;
  role: UserRole;
  created_at: string;
}

export interface AuthResponse {
  data: {
    user: User;
    token: string;
  };
}

export interface LoginData {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  city: string;
  postal_code: string | null;
  accepted_privacy: boolean;
}
