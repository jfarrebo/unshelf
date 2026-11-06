import { Component, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

/**
 * Peu del lloc (Figma «Footer»). Alguns enllaços apunten a pàgines que encara no existeixen
 * (catàleg, publicar, pàgines informatives i legals): es crearan a les setmanes següents.
 */
@Component({
  selector: 'app-footer',
  imports: [RouterLink],
  templateUrl: './footer.html',
  styleUrl: './footer.scss',
})
export class Footer {
  protected readonly auth = inject(AuthService);
  protected readonly year = new Date().getFullYear();
}
