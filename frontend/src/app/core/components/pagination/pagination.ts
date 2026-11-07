import { Component, computed, input, output } from '@angular/core';
import { Params, RouterLink } from '@angular/router';

type PageItem = { type: 'page'; page: number } | { type: 'gap'; key: string };

/**
 * Paginació (Figma «Pagination List»): 1 2 … 4. Cada pàgina és un enllaç (la pàgina va a la URL),
 * de manera que es pot obrir en una pestanya nova i el botó «enrere» funciona.
 * Es mostren la primera, l'última i les veïnes de l'actual; la resta es resumeix amb «…».
 */
@Component({
  selector: 'app-pagination',
  imports: [RouterLink],
  templateUrl: './pagination.html',
  styleUrl: './pagination.scss',
})
export class Pagination {
  readonly current = input.required<number>();
  readonly last = input.required<number>();
  /** Paràmetres de la URL per a cada pàgina (els manté la pàgina que fa servir el component). */
  readonly paramsFor = input.required<(page: number) => Params>();
  /** Avisa quan l'usuari canvia de pàgina (per moure-hi el focus). */
  readonly pageChange = output<number>();

  protected readonly items = computed<PageItem[]>(() => {
    const current = this.current();
    const last = this.last();
    const pages = [...new Set([1, current - 1, current, current + 1, last])]
      .filter((page) => page >= 1 && page <= last)
      .sort((a, b) => a - b);

    const items: PageItem[] = [];
    pages.forEach((page, i) => {
      if (i > 0 && page - pages[i - 1] > 1) {
        items.push({ type: 'gap', key: `gap-${page}` });
      }
      items.push({ type: 'page', page });
    });
    return items;
  });
}
