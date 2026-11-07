import { Component, inject } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { ActivatedRouteSnapshot, NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { filter, map } from 'rxjs';
import { Footer } from './core/layout/footer/footer';
import { Header } from './core/layout/header/header';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, Header, Footer],
  templateUrl: './app.html',
  styleUrl: './app.scss',
  host: { '[class.is-surface]': 'surfaceBackground()' },
})
export class App {
  private readonly router = inject(Router);

  /**
   * Fons blanc per a les pàgines que ho indiquen a la ruta (data: { surface: true }),
   * com la portada i el catàleg (Figma); la resta fan servir el gris clar.
   */
  protected readonly surfaceBackground = toSignal(
    this.router.events.pipe(
      filter((event) => event instanceof NavigationEnd),
      map(() => {
        let route: ActivatedRouteSnapshot = this.router.routerState.snapshot.root;
        while (route.firstChild) {
          route = route.firstChild;
        }
        return route.data['surface'] === true;
      }),
    ),
    { initialValue: false },
  );
}
