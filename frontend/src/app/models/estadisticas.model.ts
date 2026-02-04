export interface EstadisticasPorTipo {
  tipo_contrato: string;
  total: number;
  importe_total: number;
}

export interface EstadisticasPorProvincia {
  provincia: string;
  total: number;
}

export interface EstadisticasEvolucion {
  mes: string;
  total: number;
  importe_total: number;
}

export interface EstadisticasTotales {
  total_licitaciones: number;
  importe_total: number;
  abiertas: number;
}

export interface Estadisticas {
  porTipo: EstadisticasPorTipo[];
  porProvincia: EstadisticasPorProvincia[];
  evolucion: EstadisticasEvolucion[];
  totales: EstadisticasTotales;
}

export interface LicitacionReciente {
  id: number;
  expediente: string;
  titulo: string;
  estado: string;
  tipoContrato: string;
  importeSinIva: number;
  provincia: string;
  fechaPublicacion: string;
  fechaLimite: string;
  organo: string;
  diasRestantes?: number;
  urlLicitacion?: string;
}
