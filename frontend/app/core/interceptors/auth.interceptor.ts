import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthService } from '../services/auth.service';

/**
 * Afegeix el token (Authorization: Bearer) i la capçalera Accept a totes les peticions a l'API.
 * Si l'API respon 401 (token caducat o revocat), tanca la sessió i porta a l'inici de sessió.
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  if (!req.url.startsWith(environment.apiUrl)) {
    return next(req);
  }

  const auth = inject(AuthService);
  const router = inject(Router);
  const token = auth.token;

  let headers = req.headers.set('Accept', 'application/json');
  if (token) {
    headers = headers.set('Authorization', `Bearer ${token}`);
  }

  return next(req.clone({ headers })).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 401 && token) {
        auth.clearSession();
        router.navigate(['/inici-sessio']);
      }
      return throwError(() => error);
    }),
  );
};
