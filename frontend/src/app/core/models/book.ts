/** Tipus del catàleg tal com els retorna l'API (BookResource, CopyResource, GenreResource…). */

export type Language = 'catala' | 'castella' | 'angles' | 'frances';
export type CopyCondition = 'com_nou' | 'bon_estat' | 'acceptable';
export type CopyMode = 'prestec' | 'donacio';
export type CopyStatus = 'disponible' | 'pausat' | 'reservat' | 'en_prestec' | 'cedit' | 'retirat';
export type RequestStatus =
  'pendent' | 'acceptada' | 'en_prestec' | 'rebutjada' | 'cancellada' | 'finalitzada';

/** Sol·licituds en curs (les altres ja s'han tancat). */
export const ACTIVE_REQUEST_STATUSES: RequestStatus[] = ['pendent', 'acceptada', 'en_prestec'];

export interface Genre {
  id_genre: number;
  name: string;
  slug: string;
}

/** Dades públiques d'un usuari: mai el correu ni el codi postal. */
export interface PublicUser {
  id_user: number;
  name: string;
  city: string;
  avatar_url: string | null;
}

/** Sol·licitud de l'usuari actual sobre un exemplar (l'afegirà el back-end a la fitxa). */
export interface MyRequest {
  id_book_request: number;
  status: RequestStatus;
}

export interface Copy {
  id_copy: number;
  condition: CopyCondition;
  mode: CopyMode;
  max_loan_weeks: number | null;
  status: CopyStatus;
  photo_url: string | null;
  notes: string | null;
  created_at: string;
  owner?: PublicUser;
  my_request?: MyRequest | null;
}

export interface Book {
  id_book: number;
  title: string;
  slug: string;
  author: string;
  isbn: string | null;
  language: Language;
  synopsis: string | null;
  cover_url: string | null;
  is_featured: boolean;
  genre?: Genre | null;
  available_copies_count?: number;
  copies?: Copy[];
}

/** Resposta paginada de Laravel */
export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
}
