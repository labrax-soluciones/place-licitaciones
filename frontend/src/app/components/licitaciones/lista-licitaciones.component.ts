import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { Licitacion, FiltrosLicitacion } from '../../models/licitacion.model';

@Component({
  selector: 'app-lista-licitaciones',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  template: `
    <div class="licitaciones-page">
      <h1>Licitaciones</h1>

      <!-- Filtros -->
      <div class="filtros-panel">
        <div class="filtros-row">
          <div class="filtro-group">
            <label>Buscar</label>
            <input type="text" [(ngModel)]="filtros.titulo" placeholder="Título o descripción..."
                   (keyup.enter)="buscar()">
          </div>

          <div class="filtro-group">
            <label>Tipo de Contrato</label>
            <select [(ngModel)]="filtros.tipoContrato">
              <option value="">Todos</option>
              <option *ngFor="let tipo of tiposContrato" [value]="tipo.valor">
                {{ tipo.descripcion }}
              </option>
            </select>
          </div>

          <div class="filtro-group">
            <label>Estado</label>
            <select [(ngModel)]="filtros.estado">
              <option value="">Todos</option>
              <option *ngFor="let estado of estados" [value]="estado.valor">
                {{ estado.descripcion }}
              </option>
            </select>
          </div>

          <div class="filtro-group">
            <label>Provincia</label>
            <select [(ngModel)]="filtros.provincia">
              <option value="">Todas</option>
              <option *ngFor="let prov of provincias" [value]="prov">{{ prov }}</option>
            </select>
          </div>
        </div>

        <div class="filtros-row">
          <div class="filtro-group">
            <label>Importe mínimo (€)</label>
            <input type="number" [(ngModel)]="importeMin" placeholder="0">
          </div>

          <div class="filtro-group">
            <label>Importe máximo (€)</label>
            <input type="number" [(ngModel)]="importeMax" placeholder="Sin límite">
          </div>

          <div class="filtro-group checkbox-group">
            <label>
              <input type="checkbox" [(ngModel)]="soloAbiertas">
              Solo licitaciones abiertas
            </label>
          </div>

          <div class="filtro-actions">
            <button class="btn btn-primary" (click)="buscar()">Buscar</button>
            <button class="btn btn-secondary" (click)="limpiarFiltros()">Limpiar</button>
          </div>
        </div>
      </div>

      <!-- Resultados -->
      <div class="resultados">
        <div class="resultados-header">
          <span class="total">{{ totalItems | number }} licitaciones encontradas</span>
          <div class="paginacion" *ngIf="totalPages > 1">
            <button (click)="cambiarPagina(currentPage - 1)" [disabled]="currentPage === 1">←</button>
            <span>Página {{ currentPage }} de {{ totalPages }}</span>
            <button (click)="cambiarPagina(currentPage + 1)" [disabled]="currentPage === totalPages">→</button>
          </div>
        </div>

        <div class="licitaciones-grid">
          <div *ngFor="let lic of licitaciones" class="licitacion-card" [routerLink]="['/licitaciones', lic.id]">
            <div class="card-header">
              <span class="expediente">{{ lic.expediente }}</span>
              <span class="estado" [class]="'estado-' + lic.estado?.toLowerCase()">
                {{ lic.estadoDescripcion || lic.estado }}
              </span>
            </div>

            <h3 class="titulo">{{ lic.titulo }}</h3>

            <div class="card-info">
              <div class="info-row">
                <span class="label">Órgano:</span>
                <span class="value">{{ lic.organoContratante?.nombre }}</span>
              </div>
              <div class="info-row">
                <span class="label">Tipo:</span>
                <span class="badge tipo">{{ lic.tipoContratoDescripcion }}</span>
              </div>
              <div class="info-row" *ngIf="lic.provincia">
                <span class="label">Provincia:</span>
                <span class="value">{{ lic.provincia }}</span>
              </div>
            </div>

            <div class="card-footer">
              <div class="importe" *ngIf="lic.importeSinIva">
                {{ lic.importeSinIva | currency:'EUR':'symbol':'1.0-0' }}
              </div>
              <div class="fechas">
                <div *ngIf="lic.fechaPublicacion" class="fecha">
                  <span class="fecha-label">Publicación:</span>
                  {{ lic.fechaPublicacion | date:'dd/MM/yyyy' }}
                </div>
                <div *ngIf="lic.fechaLimitePresentacion" class="fecha"
                     [class.urgente]="isUrgente(lic.fechaLimitePresentacion)">
                  <span class="fecha-label">Límite:</span>
                  {{ lic.fechaLimitePresentacion | date:'dd/MM/yyyy HH:mm' }}
                </div>
              </div>
            </div>

            <div class="cpv-tags" *ngIf="lic.codigosCpv?.length">
              <span *ngFor="let cpv of lic.codigosCpv?.slice(0, 3)" class="cpv-tag">{{ cpv }}</span>
              <span *ngIf="(lic.codigosCpv?.length || 0) > 3" class="cpv-more">
                +{{ (lic.codigosCpv?.length || 0) - 3 }} más
              </span>
            </div>
          </div>
        </div>

        <div class="loading" *ngIf="loading">Cargando licitaciones...</div>
        <div class="no-results" *ngIf="!loading && licitaciones.length === 0">
          No se encontraron licitaciones con los filtros seleccionados.
        </div>
      </div>
    </div>
  `,
  styles: [`
    .licitaciones-page {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    h1 {
      color: #1e3a5f;
      margin-bottom: 1.5rem;
    }

    .filtros-panel {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .filtros-row {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;

      &:last-child { margin-bottom: 0; }
    }

    .filtro-group {
      flex: 1;
      min-width: 180px;

      label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
      }

      input, select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;

        &:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
      }

      &.checkbox-group {
        display: flex;
        align-items: flex-end;

        label {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          cursor: pointer;
        }
      }
    }

    .filtro-actions {
      display: flex;
      align-items: flex-end;
      gap: 0.5rem;
    }

    .btn {
      padding: 0.5rem 1.25rem;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all 0.2s;

      &.btn-primary {
        background: #3b82f6;
        color: white;
        &:hover { background: #2563eb; }
      }

      &.btn-secondary {
        background: #e5e7eb;
        color: #374151;
        &:hover { background: #d1d5db; }
      }
    }

    .resultados-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .total {
      color: #6b7280;
      font-size: 0.875rem;
    }

    .paginacion {
      display: flex;
      align-items: center;
      gap: 0.75rem;

      button {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 6px;
        cursor: pointer;

        &:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        &:hover:not(:disabled) {
          background: #f3f4f6;
        }
      }

      span {
        font-size: 0.875rem;
        color: #6b7280;
      }
    }

    .licitaciones-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 1.25rem;
    }

    .licitacion-card {
      background: white;
      border-radius: 12px;
      padding: 1.25rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      cursor: pointer;
      transition: all 0.2s;
      border: 2px solid transparent;

      &:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        border-color: #3b82f6;
      }
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.75rem;
    }

    .expediente {
      font-family: monospace;
      font-size: 0.875rem;
      color: #6b7280;
    }

    .estado {
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;

      &.estado-pub { background: #dbeafe; color: #1e40af; }
      &.estado-ev { background: #fef3c7; color: #92400e; }
      &.estado-adj, &.estado-res { background: #dcfce7; color: #166534; }
      &.estado-anu { background: #fee2e2; color: #991b1b; }
    }

    .titulo {
      font-size: 1rem;
      font-weight: 600;
      color: #1e3a5f;
      margin-bottom: 1rem;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-info {
      margin-bottom: 1rem;
    }

    .info-row {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;

      .label {
        color: #6b7280;
        flex-shrink: 0;
      }

      .value {
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
    }

    .badge {
      padding: 0.125rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;

      &.tipo {
        background: #f3f4f6;
        color: #374151;
      }
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      padding-top: 0.75rem;
      border-top: 1px solid #e5e7eb;
    }

    .importe {
      font-size: 1.25rem;
      font-weight: 700;
      color: #059669;
    }

    .fechas {
      text-align: right;
    }

    .fecha {
      font-size: 0.75rem;
      color: #6b7280;

      &.urgente {
        color: #dc2626;
        font-weight: 600;
      }
    }

    .fecha-label {
      color: #9ca3af;
    }

    .cpv-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.75rem;
    }

    .cpv-tag {
      padding: 0.25rem 0.5rem;
      background: #f3f4f6;
      border-radius: 4px;
      font-size: 0.7rem;
      font-family: monospace;
      color: #6b7280;
    }

    .cpv-more {
      font-size: 0.7rem;
      color: #9ca3af;
    }

    .loading, .no-results {
      text-align: center;
      padding: 3rem;
      color: #6b7280;
    }
  `]
})
export class ListaLicitacionesComponent implements OnInit {
  licitaciones: Licitacion[] = [];
  loading = true;

  filtros: FiltrosLicitacion = {};
  importeMin: number | null = null;
  importeMax: number | null = null;
  soloAbiertas = false;

  tiposContrato: { valor: string; descripcion: string }[] = [];
  estados: { valor: string; descripcion: string }[] = [];
  provincias: string[] = [];

  currentPage = 1;
  itemsPerPage = 12;
  totalItems = 0;
  totalPages = 1;

  constructor(private api: ApiService) {
    this.tiposContrato = this.api.getTiposContrato();
    this.estados = this.api.getEstados();
    this.provincias = this.api.getProvincias();
  }

  ngOnInit(): void {
    this.buscar();
  }

  buscar(): void {
    this.loading = true;
    this.currentPage = 1;
    this.cargarLicitaciones();
  }

  cargarLicitaciones(): void {
    const filtros: FiltrosLicitacion = {
      ...this.filtros,
      page: this.currentPage,
      itemsPerPage: this.itemsPerPage
    };

    if (this.importeMin) {
      filtros.importeSinIva = { ...filtros.importeSinIva, gte: this.importeMin };
    }
    if (this.importeMax) {
      filtros.importeSinIva = { ...filtros.importeSinIva, lte: this.importeMax };
    }
    if (this.soloAbiertas) {
      filtros.fechaLimitePresentacion = { after: new Date().toISOString().split('T')[0] };
    }

    this.api.getLicitaciones(filtros).subscribe({
      next: (response) => {
        this.licitaciones = response.member || [];
        this.totalItems = response.totalItems || 0;
        this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
        this.loading = false;
      },
      error: (err) => {
        console.error('Error cargando licitaciones:', err);
        this.loading = false;
      }
    });
  }

  cambiarPagina(pagina: number): void {
    if (pagina >= 1 && pagina <= this.totalPages) {
      this.currentPage = pagina;
      this.cargarLicitaciones();
    }
  }

  limpiarFiltros(): void {
    this.filtros = {};
    this.importeMin = null;
    this.importeMax = null;
    this.soloAbiertas = false;
    this.buscar();
  }

  isUrgente(fecha: string): boolean {
    if (!fecha) return false;
    const diff = new Date(fecha).getTime() - new Date().getTime();
    const dias = diff / (1000 * 60 * 60 * 24);
    return dias > 0 && dias <= 5;
  }
}
