import { Component, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

/** Pàgina d'inici provisional: el contingut definitiu (home de visitant / home personal) arriba a la PAC 3. */
@Component({
  selector: 'app-home',
  imports: [RouterLink],
  template: `
    @if (auth.user(); as user) {
      <h1>Hola, {{ user.name }}</h1>
      <p>Aquesta serà la teva pàgina d'inici personal.</p>
    } @else {
      <h1>Dona una segona vida als teus llibres</h1>
      <p>Deixa i regala llibres a persones de la teva zona. Gratis.</p>
      <p><a routerLink="/registre">Registra't</a> · <a routerLink="/inici-sessio">Inicia la sessió</a></p>
    }
  `,
})
export class Home {
  protected readonly auth = inject(AuthService);
}
