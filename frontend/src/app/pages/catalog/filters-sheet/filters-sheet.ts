import { Component, ElementRef, computed, input, output, signal, viewChild } from '@angular/core';
import { Genre } from '../../../core/models/book';
import { EMPTY_SHEET_FILTERS, SheetFilters } from '../../../core/models/catalog-filters';
import { CONDITION_LABELS, LANGUAGE_LABELS, MODE_LABELS } from '../../../core/models/labels';

/** Gèneres visibles abans de «Veure més» (Figma en mostra 4). */
const GENRES_PREVIEW = 4;

/**
 * Panell de filtres del catàleg (Figma «Catàleg · Filtres»), fet amb <dialog> natiu:
 * showModal() deixa inert la resta de la pàgina, Escape el tanca i, en tancar-lo,
 * el navegador torna el focus al botó que l'ha obert.
 *
 * Els canvis es fan sobre un esborrany i només s'apliquen amb «Aplicar els filtres»;
 * si l'usuari tanca el panell, no canvia res.
 */
@Component({
  selector: 'app-filters-sheet',
  templateUrl: './filters-sheet.html',
})
export class FiltersSheet {
  readonly genres = input.required<Genre[]>();
  readonly value = input.required<SheetFilters>();
  readonly apply = output<SheetFilters>();

  private readonly dialog = viewChild.required<ElementRef<HTMLDialogElement>>('dialog');

  protected readonly draft = signal<SheetFilters>(EMPTY_SHEET_FILTERS);
  protected readonly showAllGenres = signal(false);

  protected readonly conditionOptions = Object.entries(CONDITION_LABELS);
  protected readonly modeOptions = Object.entries(MODE_LABELS);
  protected readonly languageOptions = Object.entries(LANGUAGE_LABELS);

  /**
   * Plegat: els 4 primers gèneres i, a més, els seleccionats (perquè una opció marcada no desaparegui).
   * Desplegat («Veure més»): tots.
   */
  protected readonly visibleGenres = computed(() => {
    const genres = this.genres();
    if (this.showAllGenres()) {
      return genres;
    }
    const selected = this.draft().genres;
    return genres.filter((genre, i) => i < GENRES_PREVIEW || selected.includes(genre.slug));
  });

  /** «Veure més / Veure menys» només si hi ha més gèneres dels que es mostren plegats. */
  protected readonly canToggleGenres = computed(() => this.genres().length > GENRES_PREVIEW);

  open(): void {
    this.draft.set(structuredClone(this.value()));
    this.showAllGenres.set(false);
    this.dialog().nativeElement.showModal();
  }

  close(): void {
    this.dialog().nativeElement.close();
  }

  /** Clic al fons fosc (fora del panell): tanca. */
  protected onDialogClick(event: MouseEvent): void {
    if (event.target === this.dialog().nativeElement) {
      this.close();
    }
  }

  protected isSelected(group: keyof SheetFilters, value: string): boolean {
    return (this.draft()[group] as string[]).includes(value);
  }

  protected toggle(group: keyof SheetFilters, value: string): void {
    this.draft.update((draft) => {
      const values = draft[group] as string[];
      const next = values.includes(value) ? values.filter((v) => v !== value) : [...values, value];
      return { ...draft, [group]: next };
    });
  }

  /** «Restablir els filtres»: buida l'esborrany (s'aplica amb el botó del peu). */
  protected reset(): void {
    this.draft.set(EMPTY_SHEET_FILTERS);
  }

  protected submit(event: Event): void {
    event.preventDefault();
    this.apply.emit(this.draft());
    this.close();
  }
}
