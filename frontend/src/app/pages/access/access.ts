import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

/**
 * Pàgina d'accés (Figma «Accés · Selecció»): l'usuari tria entre crear un compte o iniciar la sessió.
 * És on porta la icona d'usuari de la capçalera quan no hi ha sessió.
 */
@Component({
  selector: 'app-access',
  imports: [RouterLink],
  templateUrl: './access.html',
})
export class Access {}
