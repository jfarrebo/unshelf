import { ParamMap, Params } from '@angular/router';
import { CopyCondition, CopyMode, Language } from './book';
import { CONDITION_LABELS, LANGUAGE_LABELS, MODE_LABELS } from './labels';

export type CatalogSort = 'rellevancia' | 'recents' | 'titol';

export const SORT_LABELS: Record<CatalogSort, string> = {
  rellevancia: 'Rellevants',
  recents: 'Més recents',
  titol: 'Títol (A-Z)',
};

/** Estat del catàleg: cerca, zona, filtres, ordre i pàgina. */
export interface CatalogFilters {
  q: string;
  location: string;
  conditions: CopyCondition[];
  genres: string[]; // slugs
  modes: CopyMode[];
  languages: Language[];
  sort: CatalogSort;
  page: number;
}

/** Només els filtres del panell (el que s'aplica amb «Aplicar els filtres»). */
export type SheetFilters = Pick<CatalogFilters, 'conditions' | 'genres' | 'modes' | 'languages'>;

export const EMPTY_SHEET_FILTERS: SheetFilters = { conditions: [], genres: [], modes: [], languages: [] };

/**
 * L'estat del catàleg viu a la URL (/llibres?zona=Girona&genere=poesia&pagina=2):
 * es pot compartir, el botó «enrere» funciona i el cercador de la capçalera només ha de posar ?q=.
 * Noms dels paràmetres de la URL, en català.
 */
const KEYS = {
  q: 'q',
  location: 'zona',
  conditions: 'estat',
  genres: 'genere',
  modes: 'modalitat',
  languages: 'idioma',
  sort: 'ordre',
  page: 'pagina',
} as const;

/** Llegeix els filtres de la URL. Els valors desconeguts s'ignoren. */
export function filtersFromParams(params: ParamMap): CatalogFilters {
  const allowed = <T extends string>(key: string, values: Record<T, string>): T[] =>
    params.getAll(key).filter((v): v is T => v in values);

  const sort = params.get(KEYS.sort) ?? '';
  const page = Number(params.get(KEYS.page));

  return {
    q: params.get(KEYS.q)?.trim() ?? '',
    location: params.get(KEYS.location)?.trim() ?? '',
    conditions: allowed(KEYS.conditions, CONDITION_LABELS),
    genres: params.getAll(KEYS.genres),
    modes: allowed(KEYS.modes, MODE_LABELS),
    languages: allowed(KEYS.languages, LANGUAGE_LABELS),
    sort: sort in SORT_LABELS ? (sort as CatalogSort) : 'rellevancia',
    page: Number.isInteger(page) && page > 1 ? page : 1,
  };
}

/** Converteix els filtres en paràmetres de la URL; els valors per defecte no s'hi escriuen. */
export function paramsFromFilters(f: CatalogFilters): Params {
  return {
    [KEYS.q]: f.q || null,
    [KEYS.location]: f.location || null,
    [KEYS.conditions]: f.conditions.length ? f.conditions : null,
    [KEYS.genres]: f.genres.length ? f.genres : null,
    [KEYS.modes]: f.modes.length ? f.modes : null,
    [KEYS.languages]: f.languages.length ? f.languages : null,
    [KEYS.sort]: f.sort === 'rellevancia' ? null : f.sort,
    [KEYS.page]: f.page > 1 ? f.page : null,
  };
}

/** Nombre de filtres del panell actius (per al comptador del botó). */
export function countSheetFilters(f: SheetFilters): number {
  return f.conditions.length + f.genres.length + f.modes.length + f.languages.length;
}
