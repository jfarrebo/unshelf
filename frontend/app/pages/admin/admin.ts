import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { environment } from '../../../environments/environment';

/** Panell d'administració provisional: comprova que el control de rols funciona també a l'API. */
@Component({
  selector: 'app-admin',
  template: `
    <h1>Administració</h1>
    <p>{{ message() }}</p>
  `,
})
export class Admin {
  private readonly http = inject(HttpClient);
  readonly message = signal('Comprovant l’accés…');

  constructor() {
    this.http.get<{ message: string }>(`${environment.apiUrl}/admin/ping`).subscribe({
      next: (r) => this.message.set(r.message),
      error: () => this.message.set("No tens accés a l'administració."),
    });
  }
}
