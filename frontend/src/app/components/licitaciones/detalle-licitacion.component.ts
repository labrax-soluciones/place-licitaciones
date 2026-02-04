import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ApiService } from '../../services/api.service';
import { Licitacion } from '../../models/licitacion.model';

@Component({
  selector: 'app-detalle-licitacion',
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `
    <div class="detalle-page" *ngIf="licitacion">
      <a routerLink="/licitaciones" class="back-link">← Volver a licitaciones</a>

      <div class="header-section">
        <div class="header-main">
          <span class="expediente">{{ licitacion.expediente }}</span>
          <span class="estado" [class]="'estado-' + licitacion.estado?.toLowerCase()">
            {{ licitacion.estadoDescripcion }}
          </span>
        </div>
        <h1>{{ licitacion.titulo }}</h1>
        <div class="header-meta">
          <span class="tipo-badge">{{ licitacion.tipoContratoDescripcion }}</span>
          <span *ngIf="licitacion.provincia">📍 {{ licitacion.provincia }}</span>
          <span *ngIf="licitacion.tipoProcedimientoDescripcion">
            {{ licitacion.tipoProcedimientoDescripcion }}
          </span>
        </div>
      </div>

      <div class="content-grid">
        <!-- Columna principal -->
        <div class="main-column">
          <!-- Importes -->
          <div class="card">
            <h2>Importes</h2>
            <div class="importes-grid">
              <div class="importe-item" *ngIf="licitacion.importeSinIva">
                <span class="importe-label">Presupuesto base (sin IVA)</span>
                <span class="importe-value">{{ licitacion.importeSinIva | currency:'EUR':'symbol':'1.2-2' }}</span>
              </div>
              <div class="importe-item" *ngIf="licitacion.importeConIva">
                <span class="importe-label">Presupuesto (con IVA)</span>
                <span class="importe-value">{{ licitacion.importeConIva | currency:'EUR':'symbol':'1.2-2' }}</span>
              </div>
              <div class="importe-item highlight" *ngIf="licitacion.importeAdjudicacion">
                <span class="importe-label">Importe adjudicación</span>
                <span class="importe-value">{{ licitacion.importeAdjudicacion | currency:'EUR':'symbol':'1.2-2' }}</span>
              </div>
            </div>
          </div>

          <!-- Descripción -->
          <div class="card" *ngIf="licitacion.descripcion">
            <h2>Objeto del contrato</h2>
            <p class="descripcion">{{ licitacion.descripcion }}</p>
          </div>

          <!-- Criterios de adjudicación -->
          <div class="card" *ngIf="licitacion.criteriosAdjudicacion?.length">
            <h2>Criterios de adjudicación</h2>
            <div class="criterios-list">
              <div *ngFor="let criterio of licitacion.criteriosAdjudicacion" class="criterio-item">
                <div class="criterio-header">
                  <span class="criterio-tipo" [class]="criterio.tipo === 'OBJ' ? 'objetivo' : 'subjetivo'">
                    {{ criterio.tipo === 'OBJ' ? 'Objetivo' : 'Subjetivo' }}
                  </span>
                  <span class="criterio-peso" *ngIf="criterio.peso">{{ criterio.peso }}%</span>
                </div>
                <p class="criterio-desc">{{ criterio.descripcion }}</p>
              </div>
            </div>
          </div>

          <!-- Documentos -->
          <div class="card" *ngIf="licitacion.documentos?.length">
            <h2>Documentación</h2>
            <div class="documentos-list">
              <a *ngFor="let doc of licitacion.documentos"
                 [href]="doc.url" target="_blank" class="documento-item">
                <span class="doc-icon">📄</span>
                <div class="doc-info">
                  <span class="doc-nombre">{{ doc.nombre }}</span>
                  <span class="doc-tipo">{{ doc.tipo }}</span>
                </div>
                <span class="doc-download">↓</span>
              </a>
            </div>
          </div>

          <!-- Adjudicación -->
          <div class="card" *ngIf="licitacion.adjudicatarioNombre">
            <h2>Adjudicación</h2>
            <div class="adjudicacion-info">
              <div class="info-row">
                <span class="label">Adjudicatario:</span>
                <span class="value strong">{{ licitacion.adjudicatarioNombre }}</span>
              </div>
              <div class="info-row" *ngIf="licitacion.adjudicatarioNif">
                <span class="label">NIF:</span>
                <span class="value">{{ licitacion.adjudicatarioNif }}</span>
              </div>
              <div class="info-row" *ngIf="licitacion.fechaAdjudicacion">
                <span class="label">Fecha:</span>
                <span class="value">{{ licitacion.fechaAdjudicacion | date:'dd/MM/yyyy' }}</span>
              </div>
              <div class="info-row" *ngIf="licitacion.numOfertas">
                <span class="label">Ofertas recibidas:</span>
                <span class="value">{{ licitacion.numOfertas }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna lateral -->
        <div class="side-column">
          <!-- Fechas -->
          <div class="card">
            <h2>Fechas clave</h2>
            <div class="fechas-list">
              <div class="fecha-item" *ngIf="licitacion.fechaPublicacion">
                <span class="fecha-label">Publicación</span>
                <span class="fecha-value">{{ licitacion.fechaPublicacion | date:'dd/MM/yyyy' }}</span>
              </div>
              <div class="fecha-item destacada" *ngIf="licitacion.fechaLimitePresentacion"
                   [class.urgente]="isUrgente()" [class.vencida]="isVencida()">
                <span class="fecha-label">Límite presentación</span>
                <span class="fecha-value">{{ licitacion.fechaLimitePresentacion | date:'dd/MM/yyyy HH:mm' }}</span>
                <span class="fecha-status" *ngIf="!isVencida() && getDiasRestantes() !== null">
                  {{ getDiasRestantes() }} días restantes
                </span>
                <span class="fecha-status" *ngIf="isVencida()">Plazo vencido</span>
              </div>
              <div class="fecha-item" *ngIf="licitacion.fechaAdjudicacion">
                <span class="fecha-label">Adjudicación</span>
                <span class="fecha-value">{{ licitacion.fechaAdjudicacion | date:'dd/MM/yyyy' }}</span>
              </div>
            </div>
          </div>

          <!-- Órgano contratante -->
          <div class="card" *ngIf="licitacion.organoContratante">
            <h2>Órgano contratante</h2>
            <div class="organo-info">
              <h3>{{ licitacion.organoContratante.nombre }}</h3>
              <div class="info-row" *ngIf="licitacion.organoContratante.nif">
                <span class="label">NIF:</span>
                <span class="value">{{ licitacion.organoContratante.nif }}</span>
              </div>
              <div class="info-row" *ngIf="licitacion.organoContratante.provincia">
                <span class="label">Provincia:</span>
                <span class="value">{{ licitacion.organoContratante.provincia }}</span>
              </div>
              <div class="info-row" *ngIf="licitacion.organoContratante.email">
                <span class="label">Email:</span>
                <a [href]="'mailto:' + licitacion.organoContratante.email" class="value link">
                  {{ licitacion.organoContratante.email }}
                </a>
              </div>
              <div class="info-row" *ngIf="licitacion.organoContratante.telefono">
                <span class="label">Teléfono:</span>
                <span class="value">{{ licitacion.organoContratante.telefono }}</span>
              </div>
            </div>
          </div>

          <!-- Códigos CPV -->
          <div class="card" *ngIf="licitacion.codigosCpv?.length">
            <h2>Códigos CPV</h2>
            <div class="cpv-list">
              <span *ngFor="let cpv of licitacion.codigosCpv" class="cpv-tag">{{ cpv }}</span>
            </div>
          </div>

          <!-- Info adicional -->
          <div class="card">
            <h2>Información adicional</h2>
            <div class="info-list">
              <div class="info-row" *ngIf="licitacion.duracionMeses">
                <span class="label">Duración:</span>
                <span class="value">{{ licitacion.duracionMeses }} meses</span>
              </div>
              <div class="info-row" *ngIf="licitacion.codigoNuts">
                <span class="label">NUTS:</span>
                <span class="value">{{ licitacion.codigoNuts }}</span>
              </div>
            </div>
            <a *ngIf="licitacion.urlLicitacion" [href]="licitacion.urlLicitacion"
               target="_blank" class="btn-place">
              Ver en PLACE →
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="loading" *ngIf="loading">Cargando licitación...</div>
  `,
  styles: [`
    .detalle-page {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    .back-link {
      display: inline-block;
      color: #3b82f6;
      text-decoration: none;
      margin-bottom: 1.5rem;
      font-weight: 500;
      &:hover { text-decoration: underline; }
    }

    .header-section {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .header-main {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .expediente {
      font-family: monospace;
      font-size: 1rem;
      color: #6b7280;
    }

    .estado {
      padding: 0.375rem 1rem;
      border-radius: 9999px;
      font-weight: 500;

      &.estado-pub { background: #dbeafe; color: #1e40af; }
      &.estado-ev { background: #fef3c7; color: #92400e; }
      &.estado-adj, &.estado-res { background: #dcfce7; color: #166534; }
      &.estado-anu { background: #fee2e2; color: #991b1b; }
    }

    h1 {
      color: #1e3a5f;
      font-size: 1.75rem;
      margin-bottom: 1rem;
      line-height: 1.3;
    }

    .header-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      color: #6b7280;
      font-size: 0.875rem;
    }

    .tipo-badge {
      background: #f3f4f6;
      padding: 0.25rem 0.75rem;
      border-radius: 4px;
      font-weight: 500;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 1.5rem;

      @media (max-width: 1024px) {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);

      h2 {
        color: #1e3a5f;
        font-size: 1.125rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
      }
    }

    .importes-grid {
      display: grid;
      gap: 1rem;
    }

    .importe-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 8px;

      &.highlight {
        background: #dcfce7;
        .importe-value { color: #166534; }
      }
    }

    .importe-label {
      color: #6b7280;
      font-size: 0.875rem;
    }

    .importe-value {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1e3a5f;
    }

    .descripcion {
      color: #374151;
      line-height: 1.6;
      white-space: pre-wrap;
    }

    .criterios-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .criterio-item {
      padding: 1rem;
      background: #f9fafb;
      border-radius: 8px;
    }

    .criterio-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
    }

    .criterio-tipo {
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 500;

      &.objetivo { background: #dbeafe; color: #1e40af; }
      &.subjetivo { background: #fef3c7; color: #92400e; }
    }

    .criterio-peso {
      font-weight: 700;
      color: #1e3a5f;
    }

    .criterio-desc {
      color: #374151;
      font-size: 0.875rem;
    }

    .documentos-list {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .documento-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 8px;
      text-decoration: none;
      color: inherit;
      transition: background 0.2s;

      &:hover { background: #e5e7eb; }
    }

    .doc-icon {
      font-size: 1.5rem;
    }

    .doc-info {
      flex: 1;
    }

    .doc-nombre {
      display: block;
      font-weight: 500;
      color: #1e3a5f;
    }

    .doc-tipo {
      font-size: 0.75rem;
      color: #6b7280;
    }

    .doc-download {
      color: #3b82f6;
      font-size: 1.25rem;
    }

    .fechas-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .fecha-item {
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 8px;

      &.destacada {
        background: #dbeafe;
        border: 2px solid #3b82f6;
      }

      &.urgente {
        background: #fef3c7;
        border-color: #f59e0b;
      }

      &.vencida {
        background: #fee2e2;
        border-color: #dc2626;
      }
    }

    .fecha-label {
      display: block;
      font-size: 0.75rem;
      color: #6b7280;
      margin-bottom: 0.25rem;
    }

    .fecha-value {
      display: block;
      font-weight: 600;
      color: #1e3a5f;
    }

    .fecha-status {
      display: block;
      font-size: 0.75rem;
      margin-top: 0.25rem;
      font-weight: 500;
    }

    .organo-info h3 {
      color: #1e3a5f;
      font-size: 1rem;
      margin-bottom: 0.75rem;
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

        &.strong { font-weight: 600; }
        &.link { color: #3b82f6; }
      }
    }

    .cpv-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .cpv-tag {
      padding: 0.375rem 0.75rem;
      background: #f3f4f6;
      border-radius: 4px;
      font-size: 0.875rem;
      font-family: monospace;
      color: #374151;
    }

    .btn-place {
      display: block;
      text-align: center;
      margin-top: 1rem;
      padding: 0.75rem;
      background: #3b82f6;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      transition: background 0.2s;

      &:hover { background: #2563eb; }
    }

    .loading {
      text-align: center;
      padding: 3rem;
      color: #6b7280;
    }
  `]
})
export class DetalleLicitacionComponent implements OnInit {
  licitacion: Licitacion | null = null;
  loading = true;

  constructor(
    private route: ActivatedRoute,
    private api: ApiService
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.api.getLicitacion(+id).subscribe({
        next: (data) => {
          this.licitacion = data;
          this.loading = false;
        },
        error: (err) => {
          console.error('Error cargando licitación:', err);
          this.loading = false;
        }
      });
    }
  }

  isUrgente(): boolean {
    const dias = this.getDiasRestantes();
    return dias !== null && dias > 0 && dias <= 5;
  }

  isVencida(): boolean {
    if (!this.licitacion?.fechaLimitePresentacion) return false;
    return new Date(this.licitacion.fechaLimitePresentacion) < new Date();
  }

  getDiasRestantes(): number | null {
    if (!this.licitacion?.fechaLimitePresentacion) return null;
    const diff = new Date(this.licitacion.fechaLimitePresentacion).getTime() - new Date().getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
  }
}
