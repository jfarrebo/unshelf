import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map, shareReplay } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Book, Genre, Paginated } from '../models/book';
import { CatalogFilters } from '../models/catalog-filters';

/**
 * Catàleg públic: gèneres, llistat de llibres i fitxa.
 * Les dades públiques no necessiten sessió; si n'hi ha, l'interceptor afegeix el token
 * (la fitxa el fa servir per saber si l'usuari ja ha sol·licitat un exemplar).
 */
@Injectable({ providedIn: 'root' })
export class CatalogService {
  private readonly http = inject(HttpClient);
  private readonly api = environment.apiUrl;

  /** Els gèneres gairebé no canvien: es demanen una vegada i es reaprofiten. */
  private readonly genres$ = this.http
    .get<{ data: Genre[] }>(`${this.api}/genres`)
    .pipe(map((response) => response.data), shareReplay({ bufferSize: 1, refCount: false }));

  genres(): Observable<Genre[]> {
    return this.genres$;
  }

  /** GET /api/books amb la cerca, els filtres, l'ordre i la pàgina. */
  books(filters: CatalogFilters): Observable<Paginated<Book>> {
    let params = new HttpParams().set('page', filters.page).set('sort', filters.sort);

    if (filters.q) {
      params = params.set('q', filters.q);
    }
    if (filters.location) {
      params = params.set('location', filters.location);
    }
    // Llistes amb la sintaxi de Laravel: condition[]=com_nou&condition[]=bon_estat
    const lists = {
      condition: filters.conditions,
      genre: filters.genres,
      mode: filters.modes,
      language: filters.languages,
    };
    for (const [key, values] of Object.entries(lists)) {
      for (const value of values) {
        params = params.append(`${key}[]`, value);
      }
    }

    return this.http.get<Paginated<Book>>(`${this.api}/books`, { params });
  }

  /** GET /api/books/{slug}: fitxa amb els exemplars visibles. */
  book(slug: string): Observable<Book> {
    return this.http
      .get<{ data: Book }>(`${this.api}/books/${encodeURIComponent(slug)}`)
      .pipe(map((response) => response.data));
  }
}
