import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Licitacion, LicitacionListResponse, FiltrosLicitacion } from '../models/licitacion.model';
import { Estadisticas, LicitacionReciente } from '../models/estadisticas.model';
import { Alerta, AlertaCreate } from '../models/alerta.model';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly API_URL = 'http://localhost:8500/api';

  constructor(private http: HttpClient) {}

  // Licitaciones
  getLicitaciones(filtros: FiltrosLicitacion = {}): Observable<LicitacionListResponse> {
    let params = new HttpParams();

    if (filtros.titulo) {
      params = params.set('titulo', filtros.titulo);
    }
    if (filtros.tipoContrato) {
      params = params.set('tipoContrato', filtros.tipoContrato);
    }
    if (filtros.estado) {
      params = params.set('estado', filtros.estado);
    }
    if (filtros.provincia) {
      params = params.set('provincia', filtros.provincia);
    }
    if (filtros.importeSinIva?.gte) {
      params = params.set('importeSinIva[gte]', filtros.importeSinIva.gte.toString());
    }
    if (filtros.importeSinIva?.lte) {
      params = params.set('importeSinIva[lte]', filtros.importeSinIva.lte.toString());
    }
    if (filtros.fechaPublicacion?.after) {
      params = params.set('fechaPublicacion[after]', filtros.fechaPublicacion.after);
    }
    if (filtros.fechaPublicacion?.before) {
      params = params.set('fechaPublicacion[before]', filtros.fechaPublicacion.before);
    }
    if (filtros.fechaLimitePresentacion?.after) {
      params = params.set('fechaLimitePresentacion[after]', filtros.fechaLimitePresentacion.after);
    }
    if (filtros.page) {
      params = params.set('page', filtros.page.toString());
    }
    if (filtros.itemsPerPage) {
      params = params.set('itemsPerPage', filtros.itemsPerPage.toString());
    }

    return this.http.get<any>(`${this.API_URL}/licitacions`, { params }).pipe(
      map((response: any) => {
        // Handle both array and object responses
        if (Array.isArray(response)) {
          return { member: response, totalItems: response.length };
        }
        return {
          member: response.member || response['hydra:member'] || [],
          totalItems: response.totalItems || response['hydra:totalItems'] || 0
        };
      })
    );
  }

  getLicitacion(id: number): Observable<Licitacion> {
    return this.http.get<Licitacion>(`${this.API_URL}/licitacions/${id}`);
  }

  // Dashboard
  getEstadisticas(): Observable<Estadisticas> {
    return this.http.get<Estadisticas>(`${this.API_URL}/dashboard/estadisticas`);
  }

  getLicitacionesRecientes(): Observable<LicitacionReciente[]> {
    return this.http.get<LicitacionReciente[]>(`${this.API_URL}/dashboard/recientes`);
  }

  getLicitacionesAbiertas(): Observable<LicitacionReciente[]> {
    return this.http.get<LicitacionReciente[]>(`${this.API_URL}/dashboard/abiertas`);
  }

  // Alertas
  getAlertas(): Observable<Alerta[]> {
    return this.http.get<any>(`${this.API_URL}/alertas`, {
      headers: { 'Accept': 'application/ld+json' }
    }).pipe(map(response => response.member || response['hydra:member'] || []));
  }

  getAlerta(id: number): Observable<Alerta> {
    return this.http.get<Alerta>(`${this.API_URL}/alertas/${id}`);
  }

  createAlerta(alerta: AlertaCreate): Observable<Alerta> {
    return this.http.post<Alerta>(`${this.API_URL}/alertas`, alerta);
  }

  updateAlerta(id: number, alerta: Partial<Alerta>): Observable<Alerta> {
    return this.http.patch<Alerta>(`${this.API_URL}/alertas/${id}`, alerta, {
      headers: { 'Content-Type': 'application/merge-patch+json' }
    });
  }

  deleteAlerta(id: number): Observable<void> {
    return this.http.delete<void>(`${this.API_URL}/alertas/${id}`);
  }

  // Órganos contratantes
  getOrganos(): Observable<any[]> {
    return this.http.get<any>(`${this.API_URL}/organo_contratantes`, {
      headers: { 'Accept': 'application/ld+json' }
    }).pipe(map(response => response.member || response['hydra:member'] || []));
  }

  // Datos auxiliares
  getProvincias(): string[] {
    return [
      'Álava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Ávila',
      'Badajoz', 'Barcelona', 'Burgos', 'Cáceres', 'Cádiz', 'Cantabria',
      'Castellón', 'Ciudad Real', 'Córdoba', 'Coruña', 'Cuenca', 'Girona',
      'Granada', 'Guadalajara', 'Guipúzcoa', 'Huelva', 'Huesca', 'Jaén',
      'León', 'Lleida', 'Lugo', 'Madrid', 'Málaga', 'Murcia', 'Navarra',
      'Ourense', 'Palencia', 'Palmas', 'Pontevedra', 'Rioja', 'Salamanca',
      'Santa Cruz de Tenerife', 'Segovia', 'Sevilla', 'Soria', 'Tarragona',
      'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Vizcaya', 'Zamora', 'Zaragoza'
    ];
  }

  getTiposContrato(): { valor: string; descripcion: string }[] {
    return [
      { valor: '1', descripcion: 'Suministros' },
      { valor: '2', descripcion: 'Servicios' },
      { valor: '3', descripcion: 'Obras' },
      { valor: '21', descripcion: 'Gestión de Servicios Públicos' },
      { valor: '31', descripcion: 'Concesión de Obras' },
    ];
  }

  getEstados(): { valor: string; descripcion: string }[] {
    return [
      { valor: 'PUB', descripcion: 'Publicada' },
      { valor: 'EV', descripcion: 'En evaluación' },
      { valor: 'ADJ', descripcion: 'Adjudicada' },
      { valor: 'RES', descripcion: 'Resuelta' },
      { valor: 'ANU', descripcion: 'Anulada' },
    ];
  }

  // Categorías predefinidas
  getCategorias(): Observable<{ id: string; nombre: string }[]> {
    return this.http.get<{ id: string; nombre: string }[]>(`${this.API_URL}/categorias`);
  }

  getLicitacionesPorCategoria(categoria: string, soloAbiertas: boolean = false): Observable<any> {
    let params = new HttpParams();
    if (soloAbiertas) {
      params = params.set('soloAbiertas', 'true');
    }
    return this.http.get<any>(`${this.API_URL}/licitaciones/categoria/${categoria}`, { params });
  }
}
