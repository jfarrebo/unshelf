import { Component, input } from '@angular/core';

/**
 * Portada d'un llibre provisional: la foto o, si no n'hi ha, un marcador de posició.
 * El «llom» a l'esquerra la fa semblar un llibre. És decorativa (alt=""): el títol sempre
 * apareix al costat en text.
 */
@Component({
  selector: 'app-book-cover',
  templateUrl: './book-cover.html',
  styleUrl: './book-cover.scss',
  host: { '[class.book-cover--lg]': "size() === 'lg'" },
})
export class BookCover {
  readonly src = input<string | null>(null);
  /** sm: targeta del catàleg (77 × 100); lg: fitxa del llibre (125 × 163). */
  readonly size = input<'sm' | 'lg'>('sm');
}
