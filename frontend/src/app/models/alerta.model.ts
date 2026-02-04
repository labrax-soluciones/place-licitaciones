export interface Usuario {
  id: number;
  email: string;
  nombre?: string;
  activo: boolean;
  notificacionesEmail: boolean;
}

export interface Alerta {
  id: number;
  nombre: string;
  codigosCpv?: string[];
  tiposContrato?: string[];
  provincias?: string[];
  importeMinimo?: number;
  importeMaximo?: number;
  palabrasClave?: string;
  activa: boolean;
  notificarEmail: boolean;
  usuario?: Usuario;
  ultimaNotificacion?: string;
  totalNotificaciones: number;
}

export interface AlertaCreate {
  nombre: string;
  codigosCpv?: string[];
  tiposContrato?: string[];
  provincias?: string[];
  importeMinimo?: number;
  importeMaximo?: number;
  palabrasClave?: string;
  activa?: boolean;
  notificarEmail?: boolean;
  usuario: string; // IRI del usuario
}
