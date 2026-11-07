/** Estat d'una càrrega de dades: es fa servir per mostrar «carregant», l'error o el contingut. */
export type LoadState<T> =
  | { status: 'loading' }
  | { status: 'error'; notFound?: boolean }
  | { status: 'ok'; data: T };
