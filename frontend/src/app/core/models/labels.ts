import { CopyCondition, CopyMode, CopyStatus, Language } from './book';

/** Textos de la interfície per als valors d'enum de l'API (que són en català sense accents). */

export const LANGUAGE_LABELS: Record<Language, string> = {
  catala: 'Català',
  castella: 'Castellà',
  angles: 'Anglès',
  frances: 'Francès',
};

export const CONDITION_LABELS: Record<CopyCondition, string> = {
  com_nou: 'Com nou',
  bon_estat: 'Bon estat',
  acceptable: 'Acceptable',
};

export const MODE_LABELS: Record<CopyMode, string> = {
  prestec: 'Préstec',
  donacio: 'Donació',
};

export const STATUS_LABELS: Record<CopyStatus, string> = {
  disponible: 'Disponible',
  pausat: 'Pausat',
  reservat: 'Reservat',
  en_prestec: 'En préstec',
  cedit: 'Cedit',
  retirat: 'Retirat',
};

/** Inicials per a l'avatar quan l'usuari no té foto: «Marta Rius» → «MR». */
export function initials(name: string): string {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
}
