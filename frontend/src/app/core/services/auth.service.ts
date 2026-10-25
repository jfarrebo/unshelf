import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, catchError, map, of, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthResponse, LoginData, RegisterData, User } from '../models/user';

const TOKEN_KEY = 'unshelf_token';

/**
 * Gestiona la sessió: registre, inici i tancament de sessió i dades de l'usuari actual.
 * L'estat es desa en un signal perquè tota l'aplicació reaccioni als canvis.
 */
@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly api = environment.apiUrl;

  private readonly currentUser = signal<User | null>(null);

  readonly user = this.currentUser.asReadonly();
  readonly isLoggedIn = computed(() => this.currentUser() !== null);
  readonly isAdmin = computed(() => this.currentUser()?.role === 'admin');

  get token(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  register(data: RegisterData): Observable<User> {
    return this.http.post<AuthResponse>(`${this.api}/register`, data).pipe(
      tap((response) => this.startSession(response)),
      map((response) => response.data.user),
    );
  }

  login(data: LoginData): Observable<User> {
    return this.http.post<AuthResponse>(`${this.api}/login`, data).pipe(
      tap((response) => this.startSession(response)),
      map((response) => response.data.user),
    );
  }

  logout(): Observable<void> {
    return this.http.post<void>(`${this.api}/logout`, {}).pipe(
      catchError(() => of(undefined)), // encara que falli, la sessió es tanca al navegador
      tap(() => this.clearSession()),
    );
  }

  /** En carregar l'aplicació, recupera l'usuari si hi ha un token desat. */
  loadUser(): Observable<User | null> {
    if (!this.token) {
      return of(null);
    }
    return this.http.get<{ data: User }>(`${this.api}/me`).pipe(
      map((response) => response.data),
      tap((user) => this.currentUser.set(user)),
      catchError(() => {
        this.clearSession();
        return of(null);
      }),
    );
  }

  clearSession(): void {
    localStorage.removeItem(TOKEN_KEY);
    this.currentUser.set(null);
  }

  private startSession(response: AuthResponse): void {
    localStorage.setItem(TOKEN_KEY, response.data.token);
    this.currentUser.set(response.data.user);
  }
}
