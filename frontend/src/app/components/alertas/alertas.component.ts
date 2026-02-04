import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { Alerta, AlertaCreate } from '../../models/alerta.model';

@Component({
  selector: 'app-alertas',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="alertas-page">
      <div class="page-header">
        <h1>Mis Alertas</h1>
        <button class="btn btn-primary" (click)="showModal = true">+ Nueva Alerta</button>
      </div>

      <p class="intro">
        Configura alertas para recibir notificaciones cuando aparezcan licitaciones que cumplan tus criterios.
      </p>

      <!-- Lista de alertas -->
      <div class="alertas-list" *ngIf="alertas.length > 0">
        <div *ngFor="let alerta of alertas" class="alerta-card" [class.inactiva]="!alerta.activa">
          <div class="alerta-header">
            <h3>{{ alerta.nombre }}</h3>
            <div class="alerta-actions">
              <button class="btn-icon" (click)="toggleAlerta(alerta)" [title]="alerta.activa ? 'Desactivar' : 'Activar'">
                {{ alerta.activa ? '🔔' : '🔕' }}
              </button>
              <button class="btn-icon" (click)="eliminarAlerta(alerta)" title="Eliminar">🗑️</button>
            </div>
          </div>

          <div class="alerta-filtros">
            <div class="filtro" *ngIf="alerta.tiposContrato?.length">
              <span class="filtro-label">Tipos:</span>
              <span class="filtro-value">{{ getTiposLabel(alerta.tiposContrato) }}</span>
            </div>
            <div class="filtro" *ngIf="alerta.provincias?.length">
              <span class="filtro-label">Provincias:</span>
              <span class="filtro-value">{{ alerta.provincias?.join(', ') }}</span>
            </div>
            <div class="filtro" *ngIf="alerta.importeMinimo || alerta.importeMaximo">
              <span class="filtro-label">Importe:</span>
              <span class="filtro-value">
                {{ alerta.importeMinimo ? (alerta.importeMinimo | currency:'EUR':'symbol':'1.0-0') : '0' }}
                -
                {{ alerta.importeMaximo ? (alerta.importeMaximo | currency:'EUR':'symbol':'1.0-0') : 'Sin límite' }}
              </span>
            </div>
            <div class="filtro" *ngIf="alerta.codigosCpv?.length">
              <span class="filtro-label">CPV:</span>
              <span class="filtro-value">{{ alerta.codigosCpv?.join(', ') }}</span>
            </div>
            <div class="filtro" *ngIf="alerta.palabrasClave">
              <span class="filtro-label">Palabras clave:</span>
              <span class="filtro-value">{{ alerta.palabrasClave }}</span>
            </div>
          </div>

          <div class="alerta-stats">
            <span>{{ alerta.totalNotificaciones }} notificaciones enviadas</span>
            <span *ngIf="alerta.ultimaNotificacion">
              Última: {{ alerta.ultimaNotificacion | date:'dd/MM/yyyy HH:mm' }}
            </span>
          </div>
        </div>
      </div>

      <div class="empty-state" *ngIf="!loading && alertas.length === 0">
        <p>No tienes alertas configuradas.</p>
        <p>Crea una alerta para recibir notificaciones de nuevas licitaciones.</p>
      </div>

      <div class="loading" *ngIf="loading">Cargando alertas...</div>

      <!-- Modal Nueva Alerta -->
      <div class="modal-overlay" *ngIf="showModal" (click)="showModal = false">
        <div class="modal" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h2>Nueva Alerta</h2>
            <button class="btn-close" (click)="showModal = false">×</button>
          </div>

          <div class="modal-body">
            <div class="form-group">
              <label>Nombre de la alerta *</label>
              <input type="text" [(ngModel)]="nuevaAlerta.nombre" placeholder="Ej: Servicios IT en Madrid">
            </div>

            <div class="form-group">
              <label>Tipos de contrato</label>
              <div class="checkbox-list">
                <label *ngFor="let tipo of tiposContrato">
                  <input type="checkbox" [checked]="nuevaAlerta.tiposContrato?.includes(tipo.valor)"
                         (change)="toggleTipo(tipo.valor)">
                  {{ tipo.descripcion }}
                </label>
              </div>
            </div>

            <div class="form-group">
              <label>Provincias</label>
              <select multiple [(ngModel)]="nuevaAlerta.provincias" size="5">
                <option *ngFor="let prov of provincias" [value]="prov">{{ prov }}</option>
              </select>
              <small>Mantén Ctrl para seleccionar múltiples</small>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Importe mínimo (€)</label>
                <input type="number" [(ngModel)]="nuevaAlerta.importeMinimo" placeholder="0">
              </div>
              <div class="form-group">
                <label>Importe máximo (€)</label>
                <input type="number" [(ngModel)]="nuevaAlerta.importeMaximo" placeholder="Sin límite">
              </div>
            </div>

            <div class="form-group">
              <label>Códigos CPV (separados por comas)</label>
              <input type="text" [(ngModel)]="cpvInput" placeholder="Ej: 72000000, 48000000">
              <small>Los códigos CPV identifican el tipo de servicio/producto</small>
            </div>

            <div class="form-group">
              <label>Palabras clave (separadas por comas)</label>
              <input type="text" [(ngModel)]="nuevaAlerta.palabrasClave" placeholder="Ej: software, desarrollo, informática">
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" [(ngModel)]="nuevaAlerta.notificarEmail">
                Recibir notificaciones por email
              </label>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" (click)="showModal = false">Cancelar</button>
            <button class="btn btn-primary" (click)="crearAlerta()" [disabled]="!nuevaAlerta.nombre">
              Crear Alerta
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .alertas-page {
      padding: 2rem;
      max-width: 900px;
      margin: 0 auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    h1 {
      color: #1e3a5f;
    }

    .intro {
      color: #6b7280;
      margin-bottom: 2rem;
    }

    .btn {
      padding: 0.625rem 1.25rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all 0.2s;

      &.btn-primary {
        background: #3b82f6;
        color: white;
        &:hover { background: #2563eb; }
        &:disabled { opacity: 0.5; cursor: not-allowed; }
      }

      &.btn-secondary {
        background: #e5e7eb;
        color: #374151;
        &:hover { background: #d1d5db; }
      }
    }

    .alertas-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .alerta-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);

      &.inactiva {
        opacity: 0.6;
        background: #f9fafb;
      }
    }

    .alerta-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;

      h3 {
        color: #1e3a5f;
        margin: 0;
      }
    }

    .alerta-actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn-icon {
      background: none;
      border: none;
      font-size: 1.25rem;
      cursor: pointer;
      padding: 0.25rem;
      border-radius: 4px;
      transition: background 0.2s;

      &:hover { background: #f3f4f6; }
    }

    .alerta-filtros {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .filtro {
      font-size: 0.875rem;
    }

    .filtro-label {
      color: #6b7280;
      margin-right: 0.25rem;
    }

    .filtro-value {
      color: #374151;
      font-weight: 500;
    }

    .alerta-stats {
      display: flex;
      gap: 1.5rem;
      font-size: 0.75rem;
      color: #9ca3af;
      padding-top: 0.75rem;
      border-top: 1px solid #e5e7eb;
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
      background: white;
      border-radius: 12px;
      color: #6b7280;
    }

    .loading {
      text-align: center;
      padding: 2rem;
      color: #6b7280;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .modal {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 560px;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;

      h2 {
        margin: 0;
        color: #1e3a5f;
      }
    }

    .btn-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6b7280;
      &:hover { color: #374151; }
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      padding: 1.5rem;
      border-top: 1px solid #e5e7eb;
    }

    .form-group {
      margin-bottom: 1.25rem;

      label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
      }

      input[type="text"],
      input[type="number"],
      select {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;

        &:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
      }

      small {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #9ca3af;
      }
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .checkbox-list {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;

      label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: normal;
        cursor: pointer;
      }
    }
  `]
})
export class AlertasComponent implements OnInit {
  alertas: Alerta[] = [];
  loading = true;
  showModal = false;

  tiposContrato = [
    { valor: '1', descripcion: 'Suministros' },
    { valor: '2', descripcion: 'Servicios' },
    { valor: '3', descripcion: 'Obras' },
  ];
  provincias: string[] = [];

  nuevaAlerta: Partial<AlertaCreate> = {
    nombre: '',
    tiposContrato: [],
    provincias: [],
    codigosCpv: [],
    notificarEmail: true,
  };
  cpvInput = '';

  constructor(private api: ApiService) {
    this.provincias = this.api.getProvincias();
  }

  ngOnInit(): void {
    this.cargarAlertas();
  }

  cargarAlertas(): void {
    this.api.getAlertas().subscribe({
      next: (data) => {
        this.alertas = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Error cargando alertas:', err);
        this.loading = false;
      }
    });
  }

  toggleTipo(tipo: string): void {
    if (!this.nuevaAlerta.tiposContrato) {
      this.nuevaAlerta.tiposContrato = [];
    }
    const index = this.nuevaAlerta.tiposContrato.indexOf(tipo);
    if (index >= 0) {
      this.nuevaAlerta.tiposContrato.splice(index, 1);
    } else {
      this.nuevaAlerta.tiposContrato.push(tipo);
    }
  }

  crearAlerta(): void {
    if (!this.nuevaAlerta.nombre) return;

    const alerta: AlertaCreate = {
      nombre: this.nuevaAlerta.nombre,
      tiposContrato: this.nuevaAlerta.tiposContrato,
      provincias: this.nuevaAlerta.provincias,
      codigosCpv: this.cpvInput ? this.cpvInput.split(',').map(s => s.trim()) : [],
      importeMinimo: this.nuevaAlerta.importeMinimo,
      importeMaximo: this.nuevaAlerta.importeMaximo,
      palabrasClave: this.nuevaAlerta.palabrasClave,
      notificarEmail: this.nuevaAlerta.notificarEmail ?? true,
      usuario: '/api/usuarios/1' // TODO: Usuario actual
    };

    this.api.createAlerta(alerta).subscribe({
      next: () => {
        this.showModal = false;
        this.resetForm();
        this.cargarAlertas();
      },
      error: (err) => console.error('Error creando alerta:', err)
    });
  }

  toggleAlerta(alerta: Alerta): void {
    this.api.updateAlerta(alerta.id, { activa: !alerta.activa }).subscribe({
      next: () => this.cargarAlertas(),
      error: (err) => console.error('Error actualizando alerta:', err)
    });
  }

  eliminarAlerta(alerta: Alerta): void {
    if (confirm('¿Eliminar esta alerta?')) {
      this.api.deleteAlerta(alerta.id).subscribe({
        next: () => this.cargarAlertas(),
        error: (err) => console.error('Error eliminando alerta:', err)
      });
    }
  }

  getTiposLabel(tipos: string[] | undefined): string {
    if (!tipos) return '';
    return tipos.map(t => this.tiposContrato.find(tc => tc.valor === t)?.descripcion || t).join(', ');
  }

  resetForm(): void {
    this.nuevaAlerta = {
      nombre: '',
      tiposContrato: [],
      provincias: [],
      codigosCpv: [],
      notificarEmail: true,
    };
    this.cpvInput = '';
  }
}
