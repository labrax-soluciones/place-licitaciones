import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { BaseChartDirective } from 'ng2-charts';
import { ChartConfiguration, ChartData } from 'chart.js';
import { ApiService } from '../../services/api.service';
import { Estadisticas, LicitacionReciente } from '../../models/estadisticas.model';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink, BaseChartDirective],
  template: `
    <div class="dashboard">
      <h1>Dashboard</h1>

      <!-- Tarjetas de resumen -->
      <div class="stats-cards" *ngIf="estadisticas">
        <div class="stat-card">
          <div class="stat-value">{{ estadisticas.totales.total_licitaciones | number }}</div>
          <div class="stat-label">Total Licitaciones</div>
        </div>
        <div class="stat-card highlight">
          <div class="stat-value">{{ estadisticas.totales.abiertas | number }}</div>
          <div class="stat-label">Abiertas</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ formatImporte(estadisticas.totales.importe_total) }}</div>
          <div class="stat-label">Importe Total</div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="charts-grid" *ngIf="estadisticas">
        <div class="chart-card">
          <h3>Por Tipo de Contrato</h3>
          <canvas baseChart
            [data]="tipoChartData"
            [options]="pieChartOptions"
            type="doughnut">
          </canvas>
        </div>

        <div class="chart-card">
          <h3>Evolución Mensual</h3>
          <canvas baseChart
            [data]="evolucionChartData"
            [options]="lineChartOptions"
            type="line">
          </canvas>
        </div>

        <div class="chart-card">
          <h3>Top 10 Provincias</h3>
          <canvas baseChart
            [data]="provinciaChartData"
            [options]="barChartOptions"
            type="bar">
          </canvas>
        </div>
      </div>

      <!-- Licitaciones recientes -->
      <div class="recent-section">
        <div class="section-header">
          <h2>Licitaciones Recientes</h2>
          <a routerLink="/licitaciones" class="view-all">Ver todas →</a>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Expediente</th>
                <th>Título</th>
                <th>Órgano</th>
                <th>Tipo</th>
                <th>Importe</th>
                <th>Provincia</th>
                <th>Fecha Límite</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let lic of recientes" [routerLink]="['/licitaciones', lic.id]" class="clickable">
                <td class="expediente">{{ lic.expediente }}</td>
                <td class="titulo">{{ lic.titulo | slice:0:60 }}{{ lic.titulo.length > 60 ? '...' : '' }}</td>
                <td class="organo">{{ lic.organo | slice:0:40 }}</td>
                <td><span class="badge" [class]="'tipo-' + getTipoClass(lic.tipoContrato)">{{ lic.tipoContrato }}</span></td>
                <td class="importe">{{ lic.importeSinIva | currency:'EUR':'symbol':'1.0-0' }}</td>
                <td>{{ lic.provincia }}</td>
                <td class="fecha" [class.urgente]="isUrgente(lic.fechaLimite)">
                  {{ lic.fechaLimite | date:'dd/MM/yyyy HH:mm' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="loading" *ngIf="loading">Cargando datos...</div>
    </div>
  `,
  styles: [`
    .dashboard {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    h1 {
      color: #1e3a5f;
      margin-bottom: 1.5rem;
    }

    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      text-align: center;

      &.highlight {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
      }
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      font-size: 0.875rem;
      opacity: 0.8;
    }

    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .chart-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);

      h3 {
        color: #1e3a5f;
        margin-bottom: 1rem;
        font-size: 1rem;
      }
    }

    .recent-section {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;

      h2 {
        color: #1e3a5f;
        font-size: 1.25rem;
      }
    }

    .view-all {
      color: #2d5a87;
      text-decoration: none;
      font-weight: 500;
      &:hover { text-decoration: underline; }
    }

    .table-container {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;

      th, td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
      }

      th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
      }

      .clickable {
        cursor: pointer;
        &:hover { background: #f9fafb; }
      }

      .expediente {
        font-family: monospace;
        font-size: 0.875rem;
      }

      .titulo {
        max-width: 300px;
      }

      .organo {
        font-size: 0.875rem;
        color: #6b7280;
      }

      .importe {
        font-weight: 600;
        text-align: right;
      }

      .fecha {
        white-space: nowrap;
        &.urgente { color: #dc2626; font-weight: 600; }
      }
    }

    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;

      &.tipo-servicios { background: #dbeafe; color: #1e40af; }
      &.tipo-suministros { background: #dcfce7; color: #166534; }
      &.tipo-obras { background: #fef3c7; color: #92400e; }
    }

    .loading {
      text-align: center;
      padding: 2rem;
      color: #6b7280;
    }
  `]
})
export class DashboardComponent implements OnInit {
  estadisticas: Estadisticas | null = null;
  recientes: LicitacionReciente[] = [];
  loading = true;

  tipoChartData: ChartData<'doughnut'> = { labels: [], datasets: [] };
  evolucionChartData: ChartData<'line'> = { labels: [], datasets: [] };
  provinciaChartData: ChartData<'bar'> = { labels: [], datasets: [] };

  pieChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  };

  lineChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    }
  };

  barChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    indexAxis: 'y',
    scales: {
      x: { beginAtZero: true }
    }
  };

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.loadData();
  }

  loadData(): void {
    this.api.getEstadisticas().subscribe({
      next: (data) => {
        this.estadisticas = data;
        this.prepareCharts();
      },
      error: (err) => console.error('Error cargando estadísticas:', err)
    });

    this.api.getLicitacionesRecientes().subscribe({
      next: (data) => {
        this.recientes = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Error cargando recientes:', err);
        this.loading = false;
      }
    });
  }

  prepareCharts(): void {
    if (!this.estadisticas) return;

    // Gráfico por tipo
    const tipoLabels: { [key: string]: string } = {
      '1': 'Suministros', '2': 'Servicios', '3': 'Obras',
      '21': 'Gestión Serv.', '31': 'Concesión'
    };

    this.tipoChartData = {
      labels: this.estadisticas.porTipo.map(t => tipoLabels[t.tipo_contrato] || t.tipo_contrato),
      datasets: [{
        data: this.estadisticas.porTipo.map(t => t.total),
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
      }]
    };

    // Gráfico evolución
    this.evolucionChartData = {
      labels: this.estadisticas.evolucion.map(e => e.mes),
      datasets: [{
        label: 'Licitaciones',
        data: this.estadisticas.evolucion.map(e => e.total),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.3
      }]
    };

    // Gráfico provincias
    this.provinciaChartData = {
      labels: this.estadisticas.porProvincia.map(p => p.provincia),
      datasets: [{
        label: 'Licitaciones',
        data: this.estadisticas.porProvincia.map(p => p.total),
        backgroundColor: '#3b82f6'
      }]
    };
  }

  formatImporte(importe: number): string {
    if (importe >= 1e9) return (importe / 1e9).toFixed(1) + ' B€';
    if (importe >= 1e6) return (importe / 1e6).toFixed(1) + ' M€';
    if (importe >= 1e3) return (importe / 1e3).toFixed(0) + ' K€';
    return importe?.toFixed(0) + ' €';
  }

  getTipoClass(tipo: string): string {
    if (tipo?.includes('Servicio')) return 'servicios';
    if (tipo?.includes('Suministro')) return 'suministros';
    if (tipo?.includes('Obra')) return 'obras';
    return 'otros';
  }

  isUrgente(fecha: string): boolean {
    if (!fecha) return false;
    const diff = new Date(fecha).getTime() - new Date().getTime();
    const dias = diff / (1000 * 60 * 60 * 24);
    return dias > 0 && dias <= 5;
  }
}
