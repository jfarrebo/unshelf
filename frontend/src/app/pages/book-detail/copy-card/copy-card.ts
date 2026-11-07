import { Component, computed, input } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Copy } from '../../../core/models/book';
import { CONDITION_LABELS, STATUS_LABELS, initials } from '../../../core/models/labels';

/**
 * Exemplar a la fitxa del llibre (Figma «Card · Owner»). Quatre variants:
 * - visitant: només la informació (sense botó);
 * - usuari, exemplar d'una altra persona i disponible: «Sol·licitar»;
 * - usuari que ja l'ha sol·licitat: etiqueta «Sol·licitat» i «Veure la sol·licitud»;
 * - propietari: targeta destacada «El teu exemplar» i «Edita l'exemplar».
 */
@Component({
  selector: 'app-copy-card',
  imports: [RouterLink],
  templateUrl: './copy-card.html',
  styleUrl: './copy-card.scss',
})
export class CopyCard {
  readonly copy = input.required<Copy>();
  /** Usuari amb la sessió iniciada (null si és un visitant). */
  readonly currentUserId = input<number | null>(null);

  protected readonly isOwn = computed(() => this.copy().owner?.id_user === this.currentUserId());
  protected readonly isLoggedIn = computed(() => this.currentUserId() !== null);
  /** Sol·licitud activa de l'usuari sobre aquest exemplar (el camp l'afegirà el back-end). */
  protected readonly myRequest = computed(() => {
    const request = this.copy().my_request;
    return request && ['pendent', 'acceptada'].includes(request.status) ? request : null;
  });

  protected readonly ownerName = computed(() => this.copy().owner?.name ?? 'Usuari');
  protected readonly ownerInitials = computed(() => initials(this.ownerName()));
  protected readonly statusLabel = computed(() => STATUS_LABELS[this.copy().status]);
  protected readonly conditionLabel = computed(() => CONDITION_LABELS[this.copy().condition]);

  /** «Préstec · màxim 4 setmanes» o «Donació». */
  protected readonly terms = computed(() => {
    const copy = this.copy();
    if (copy.mode === 'donacio') {
      return 'Donació';
    }
    return copy.max_loan_weeks ? `Préstec · màxim ${copy.max_loan_weeks} setmanes` : 'Préstec';
  });

  protected readonly canRequest = computed(
    () => this.isLoggedIn() && !this.isOwn() && !this.myRequest() && this.copy().status === 'disponible',
  );
}
