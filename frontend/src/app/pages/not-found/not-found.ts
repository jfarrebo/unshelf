import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-not-found',
  imports: [RouterLink],
  template: `
    <h1>Pàgina no trobada</h1>
    <p>La pàgina que busques no existeix.</p>
    <p><a routerLink="/">Torna a l'inici</a></p>
  `,
})
export class NotFound {}
