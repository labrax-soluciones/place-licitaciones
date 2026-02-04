import { Routes } from '@angular/router';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ListaLicitacionesComponent } from './components/licitaciones/lista-licitaciones.component';
import { DetalleLicitacionComponent } from './components/licitaciones/detalle-licitacion.component';
import { AlertasComponent } from './components/alertas/alertas.component';

export const routes: Routes = [
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'licitaciones', component: ListaLicitacionesComponent },
  { path: 'licitaciones/:id', component: DetalleLicitacionComponent },
  { path: 'alertas', component: AlertasComponent },
  { path: '**', redirectTo: '/dashboard' }
];
