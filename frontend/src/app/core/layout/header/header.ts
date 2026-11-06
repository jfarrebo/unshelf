import { Component, ElementRef, computed, effect, inject, signal, viewChild } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { NavigationEnd, Router, RouterLink, RouterLinkActive } from '@angular/router';
import { filter, map } from 'rxjs';
import { AuthService } from '../../services/auth.service';

/** Pàgines d'accés: la icona d'usuari es mostra activa. */
const ACCESS_PAGES = ['/acces', '/inici-sessio', '/registre'];

/**
 * Capçalera del lloc (Figma «Header»).
 * Mòbil: logotip + usuari a dalt; menú + cercador a sota; el menú obre la navegació.
 * Escriptori (≥ 1024 px, proposta): tot en una fila i la navegació sempre visible.
 */
@Component({
  selector: 'app-header',
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './header.html',
  styleUrl: './header.scss',
  host: { '(document:keydown.escape)': 'closeMenu(true)' },
})
export class Header {
  protected readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  /** URL actual com a signal, per decidir quina variant de capçalera es mostra. */
  private readonly url = toSignal(
    this.router.events.pipe(
      filter((e): e is NavigationEnd => e instanceof NavigationEnd),
      map((e) => e.urlAfterRedirects),
    ),
    { initialValue: this.router.url },
  );

  protected readonly isAccessPage = computed(() => ACCESS_PAGES.some((page) => this.url().startsWith(page)));
  protected readonly menuOpen = signal(false);

  private readonly menuButton = viewChild<ElementRef<HTMLButtonElement>>('menuButton');

  constructor() {
    // En canviar de pàgina, el menú es tanca
    effect(() => {
      this.url();
      this.menuOpen.set(false);
    });
  }

  toggleMenu(): void {
    this.menuOpen.update((open) => !open);
  }

  /** Escape tanca el menú i torna el focus al botó que l'ha obert. */
  closeMenu(returnFocus = false): void {
    if (!this.menuOpen()) {
      return;
    }
    this.menuOpen.set(false);
    if (returnFocus) {
      this.menuButton()?.nativeElement.focus();
    }
  }

  /** El cercador porta al catàleg amb el text com a paràmetre (?q=). */
  search(event: Event, query: string): void {
    event.preventDefault();
    const q = query.trim();
    this.router.navigate(['/llibres'], { queryParams: q ? { q } : {} });
  }

  logout(): void {
    this.auth.logout().subscribe(() => this.router.navigate(['/']));
  }
}
