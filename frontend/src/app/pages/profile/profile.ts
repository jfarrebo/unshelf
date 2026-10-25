import { Component, inject } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';

/** Perfil provisional: mostra les dades de l'usuari. L'edició s'afegirà amb la resta del perfil. */
@Component({
  selector: 'app-profile',
  template: `
    <h1>El meu perfil</h1>
    @if (auth.user(); as user) {
      <dl>
        <dt>Nom</dt><dd>{{ user.name }}</dd>
        <dt>Correu electrònic</dt><dd>{{ user.email }}</dd>
        <dt>Ciutat</dt><dd>{{ user.city }}</dd>
        <dt>Codi postal</dt><dd>{{ user.postal_code ?? '—' }}</dd>
        <dt>Rol</dt><dd>{{ user.role === 'admin' ? 'Administrador' : 'Usuari' }}</dd>
      </dl>
    }
  `,
})
export class Profile {
  protected readonly auth = inject(AuthService);
}
