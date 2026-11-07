import { Component, computed, input } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Book } from '../../models/book';
import { LANGUAGE_LABELS } from '../../models/labels';
import { BookCover } from '../book-cover/book-cover';

/**
 * Targeta d'un llibre al catàleg (Figma «Card · Product»): portada, títol, autor,
 * idioma, disponibilitat i enllaç a la fitxa. El cor de preferits no es mostra (no és de la PAC 3).
 */
@Component({
  selector: 'app-book-card',
  imports: [RouterLink, BookCover],
  templateUrl: './book-card.html',
  styleUrl: './book-card.scss',
})
export class BookCard {
  readonly book = input.required<Book>();

  protected readonly language = computed(() => LANGUAGE_LABELS[this.book().language]);
  protected readonly available = computed(() => (this.book().available_copies_count ?? 0) > 0);
}
