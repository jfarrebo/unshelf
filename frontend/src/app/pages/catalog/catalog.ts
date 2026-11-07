import { Component, ElementRef, computed, effect, inject, linkedSignal, signal, viewChild } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { ActivatedRoute, Router } from '@angular/router';
import { Subject, catchError, combineLatest, map, of, startWith, switchMap } from 'rxjs';
import { BookCard } from '../../core/components/book-card/book-card';
import { Pagination } from '../../core/components/pagination/pagination';
import { Book, Paginated } from '../../core/models/book';
import {
  CatalogFilters,
  CatalogSort,
  EMPTY_SHEET_FILTERS,
  SORT_LABELS,
  SheetFilters,
  countSheetFilters,
  filtersFromParams,
  paramsFromFilters,
} from '../../core/models/catalog-filters';
import { LoadState } from '../../core/models/load-state';
import { CatalogService } from '../../core/services/catalog.service';
import { FiltersSheet } from './filters-sheet/filters-sheet';

/**
 * Catàleg i resultats de cerca (Figma «Catàleg · Llistat / Cerca / Sense resultats / Filtres»).
 *
 * Flux: URL (?q, zona, filtres, ordre, pàgina) → filtres → petició a l'API → estat (carregant / error / dades).
 * Qualsevol canvi (cerca, filtres, ordre, pàgina) només modifica la URL; la petició es fa sola.
 * switchMap cancel·la la petició anterior si l'usuari canvia de filtre abans que arribi la resposta.
 */
@Component({
  selector: 'app-catalog',
  imports: [BookCard, Pagination, FiltersSheet],
  templateUrl: './catalog.html',
  styleUrl: './catalog.scss',
})
export class Catalog {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly catalog = inject(CatalogService);

  /** Per tornar a provar la càrrega després d'un error. */
  private readonly retry$ = new Subject<void>();

  protected readonly filters = toSignal(this.route.queryParamMap.pipe(map(filtersFromParams)), {
    requireSync: true,
  });

  protected readonly genres = toSignal(this.catalog.genres().pipe(catchError(() => of([]))), {
    initialValue: [],
  });

  private readonly state = toSignal(
    combineLatest([this.route.queryParamMap, this.retry$.pipe(startWith(undefined))]).pipe(
      map(([params]) => filtersFromParams(params)),
      switchMap((filters) =>
        this.catalog.books(filters).pipe(
          map((page): LoadState<Paginated<Book>> => ({ status: 'ok', data: page })),
          catchError(() => of<LoadState<Paginated<Book>>>({ status: 'error' })),
          startWith<LoadState<Paginated<Book>>>({ status: 'loading' }),
        ),
      ),
    ),
    { initialValue: { status: 'loading' } as LoadState<Paginated<Book>> },
  );

  protected readonly loading = computed(() => this.state().status === 'loading');
  protected readonly failed = computed(() => this.state().status === 'error');
  /** Últims resultats rebuts: mentre es carreguen els nous, es continuen mostrant els anteriors. */
  protected readonly result = linkedSignal<LoadState<Paginated<Book>>, Paginated<Book> | null>({
    source: this.state,
    computation: (state, previous) => {
      if (state.status === 'ok') {
        return state.data;
      }
      return state.status === 'loading' ? (previous?.value ?? null) : null;
    },
  });

  protected readonly sheetFilters = computed<SheetFilters>(() => {
    const { conditions, genres, modes, languages } = this.filters();
    return { conditions, genres, modes, languages };
  });
  protected readonly activeFilters = computed(() => countSheetFilters(this.sheetFilters()));
  protected readonly hasAnyFilter = computed(() => this.activeFilters() > 0 || this.filters().location !== '');

  protected readonly sortOptions = Object.entries(SORT_LABELS) as [CatalogSort, string][];

  /** Targetes esquelet mentre es carrega la primera pàgina (dues files a escriptori). */
  protected readonly skeletonCards = [1, 2, 3, 4, 5, 6];

  /** Funció que la paginació fa servir per construir l'enllaç de cada pàgina. */
  protected readonly paramsForPage = (page: number) => paramsFromFilters({ ...this.filters(), page });

  private readonly sheet = viewChild.required(FiltersSheet);
  private readonly summary = viewChild<ElementRef<HTMLElement>>('summary');

  /**
   * Després de canviar de pàgina, el focus va al resum de resultats (si no, quedaria a la paginació,
   * a baix de tot). Es desa la pàgina demanada i s'hi mou el focus quan arriben les dades d'aquesta pàgina.
   */
  private readonly pendingFocusPage = signal<number | null>(null);

  constructor() {
    effect(() => {
      const pending = this.pendingFocusPage();
      if (pending !== null && !this.loading() && this.result()?.meta.current_page === pending) {
        this.summary()?.nativeElement.focus();
        this.pendingFocusPage.set(null);
      }
    });
  }

  protected openFilters(): void {
    this.sheet().open();
  }

  protected searchLocation(event: Event, location: string): void {
    event.preventDefault();
    this.navigate({ location: location.trim() });
  }

  protected sortBy(sort: string): void {
    this.navigate({ sort: sort as CatalogSort });
  }

  protected applyFilters(filters: SheetFilters): void {
    this.navigate(filters);
  }

  /** «Netejar els filtres»: treu la zona i els filtres; es manté el text cercat. */
  protected clearFilters(): void {
    this.navigate({ ...EMPTY_SHEET_FILTERS, location: '' });
  }

  protected onPageChange(page: number): void {
    this.pendingFocusPage.set(page);
  }

  protected retry(): void {
    this.retry$.next();
  }

  /** Canvia la URL; qualsevol canvi de filtre torna a la pàgina 1. */
  private navigate(changes: Partial<CatalogFilters>): void {
    const next: CatalogFilters = { ...this.filters(), ...changes, page: 1 };
    this.router.navigate([], { relativeTo: this.route, queryParams: paramsFromFilters(next) });
  }
}
