import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

type Field = 'name' | 'email' | 'password' | 'city' | 'postal_code' | 'accepted_privacy';

@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './register.html',
})
export class Register {
  private readonly fb = inject(FormBuilder);
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  readonly form = this.fb.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(100)]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    city: ['', [Validators.required, Validators.maxLength(100)]],
    postal_code: ['', Validators.maxLength(10)],
    accepted_privacy: [false, Validators.requiredTrue],
  });

  /** Errors que retorna l'API (422), per camp. */
  readonly serverErrors = signal<Partial<Record<Field, string[]>>>({});
  readonly error = signal<string | null>(null);
  readonly loading = signal(false);

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.loading.set(true);
    this.error.set(null);
    this.serverErrors.set({});

    const value = this.form.getRawValue();
    this.auth.register({ ...value, postal_code: value.postal_code || null }).subscribe({
      next: () => this.router.navigate(['/']),
      error: (e: HttpErrorResponse) => {
        this.loading.set(false);
        if (e.status === 422 && e.error?.errors) {
          this.serverErrors.set(e.error.errors);
        } else if (e.status === 429) {
          this.error.set("Massa intents. Torna-ho a provar d'aquí a un minut.");
        } else {
          this.error.set("No s'ha pogut crear el compte. Torna-ho a provar més tard.");
        }
      },
    });
  }

  showError(field: Field): boolean {
    const control = this.form.controls[field];
    return (control.invalid && control.touched) || !!this.serverErrors()[field];
  }

  serverError(field: Field): string | null {
    return this.serverErrors()[field]?.[0] ?? null;
  }
}
