import { Routes } from '@angular/router';
import { adminGuard, authGuard, guestGuard } from './core/guards/auth.guards';

export const routes: Routes = [
  {
    path: '',
    title: 'Unshelf',
    loadComponent: () => import('./pages/home/home').then((m) => m.Home),
  },
  {
    path: 'inici-sessio',
    title: 'Inicia la sessió · Unshelf',
    canActivate: [guestGuard],
    loadComponent: () => import('./pages/login/login').then((m) => m.Login),
  },
  {
    path: 'registre',
    title: "Crea el teu compte · Unshelf",
    canActivate: [guestGuard],
    loadComponent: () => import('./pages/register/register').then((m) => m.Register),
  },
  {
    path: 'perfil',
    title: 'El meu perfil · Unshelf',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/profile/profile').then((m) => m.Profile),
  },
  {
    path: 'admin',
    title: 'Administració · Unshelf',
    canActivate: [authGuard, adminGuard],
    loadComponent: () => import('./pages/admin/admin').then((m) => m.Admin),
  },
  {
    path: '**',
    title: 'Pàgina no trobada · Unshelf',
    loadComponent: () => import('./pages/not-found/not-found').then((m) => m.NotFound),
  },
];
