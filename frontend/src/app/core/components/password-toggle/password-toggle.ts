import { Component, input, signal } from '@angular/core';

/**
 * Botó per mostrar o amagar una contrasenya. Es col·loca dins d'un .field__group, al costat del camp:
 *   <input #pwd id="password" type="password" … />
 *   <app-password-toggle [input]="pwd" />
 *
 * Accessibilitat: botó de commutació (aria-pressed) amb nom fix «Mostra la contrasenya»;
 * el lector de pantalla anuncia «premut / no premut». aria-controls apunta al camp.
 */
@Component({
  selector: 'app-password-toggle',
  templateUrl: './password-toggle.html',
})
export class PasswordToggle {
  /** Camp de contrasenya que controla el botó. */
  readonly input = input.required<HTMLInputElement>();

  protected readonly visible = signal(false);

  toggle(): void {
    this.visible.update((visible) => !visible);
    this.input().type = this.visible() ? 'text' : 'password';
  }
}
