import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, effect, inject, input, signal } from '@angular/core';
import { toObservable, toSignal } from '@angular/core/rxjs-interop';
import { Title } from '@angular/platform-browser';
import { RouterLink } from '@angular/router';
import { Subject, catchError, combineLatest, map, of, startWith, switchMap } from 'rxjs';
import { BookCover } from '../../core/components/book-cover/book-cover';
import { Book } from '../../core/models/book';
import { LANGUAGE_LABELS } from '../../core/models/labels';
import { LoadState } from '../../core/models/load-state';
import { AuthService } from '../../core/services/auth.service';
import { CatalogService } from '../../core/services/catalog.service';
import { CopyCard } from './copy-card/copy-card';

/** A partir d'aquesta llargada, la sinopsi es retalla i apareix «Llegir més». */
const SYNOPSIS_PREVIEW = 280;

/**
 * Fitxa del llibre (Figma «Catàleg · Detall del llibre · Sessió iniciada / Visitant»):
 * dades del llibre, sinopsi i exemplars visibles amb el propietari.
 * El slug arriba com a input des de la ruta /llibres/:slug (withComponentInputBinding).
 */
@Component({
  selector: 'app-book-detail',
  imports: [RouterLink, BookCover, CopyCard],
  templateUrl: './book-detail.html',
  styleUrl: './book-detail.scss',
})
export class BookDetail {
  private readonly catalog = inject(CatalogService);
  private readonly auth = inject(AuthService);
  private readonly title = inject(Title);

  readonly slug = input.required<string>();

  private readonly retry$ = new Subject<void>();

  private readonly state = toSignal(
    combineLatest([toObservable(this.slug), this.retry$.pipe(startWith(undefined))]).pipe(
      switchMap(([slug]) =>
        this.catalog.book(slug).pipe(
          map((book): LoadState<Book> => ({ status: 'ok', data: book })),
          catchError((error: HttpErrorResponse) =>
            of<LoadState<Book>>({ status: 'error', notFound: error.status === 404 }),
          ),
          startWith<LoadState<Book>>({ status: 'loading' }),
        ),
      ),
    ),
    { initialValue: { status: 'loading' } as LoadState<Book> },
  );

  protected readonly loading = computed(() => this.state().status === 'loading');
  protected readonly notFound = computed(() => {
    const state = this.state();
    return state.status === 'error' && !!state.notFound;
  });
  protected readonly failed = computed(() => this.state().status === 'error' && !this.notFound());
  protected readonly book = computed(() => {
    const state = this.state();
    return state.status === 'ok' ? state.data : null;
  });

  protected readonly currentUserId = computed(() => this.auth.user()?.id_user ?? null);
  protected readonly isLoggedIn = this.auth.isLoggedIn;

  protected readonly language = computed(() => {
    const book = this.book();
    return book ? LANGUAGE_LABELS[book.language] : '';
  });
  protected readonly copies = computed(() => this.book()?.copies ?? []);
  protected readonly availableCount = computed(
    () => this.copies().filter((copy) => copy.status === 'disponible').length,
  );

  /** «3 exemplars disponibles», «1 exemplar disponible» o «Cap exemplar disponible ara mateix». */
  protected readonly copiesHeading = computed(() => {
    const n = this.availableCount();
    if (n === 0) {
      return 'Cap exemplar disponible ara mateix';
    }
    return n === 1 ? '1 exemplar disponible' : `${n} exemplars disponibles`;
  });

  protected readonly synopsisExpanded = signal(false);
  protected readonly synopsisIsLong = computed(
    () => (this.book()?.synopsis?.length ?? 0) > SYNOPSIS_PREVIEW,
  );

  constructor() {
    // Títol de la pestanya amb el nom del llibre
    effect(() => {
      const book = this.book();
      if (book) {
        this.title.setTitle(`${book.title} · Unshelf`);
      } else if (this.notFound()) {
        this.title.setTitle('Llibre no trobat · Unshelf');
      }
    });
  }

  protected retry(): void {
    this.retry$.next();
  }
}
