export interface OrganoContratante {
  id: number;
  nif: string;
  nombre: string;
  dir3?: string;
  provincia?: string;
  email?: string;
  telefono?: string;
  urlPerfil?: string;
}

export interface Licitacion {
  id: number;
  idPlace: string;
  expediente: string;
  titulo: string;
  estado: string;
  estadoDescripcion?: string;
  tipoContrato: string;
  tipoContratoDescripcion?: string;
  importeSinIva?: number;
  importeConIva?: number;
  codigosCpv?: string[];
  provincia?: string;
  codigoNuts?: string;
  tipoProcedimiento?: string;
  tipoProcedimientoDescripcion?: string;
  fechaPublicacion?: string;
  fechaLimitePresentacion?: string;
  fechaAdjudicacion?: string;
  duracionMeses?: number;
  urlLicitacion?: string;
  descripcion?: string;
  documentos?: Documento[];
  criteriosAdjudicacion?: CriterioAdjudicacion[];
  numOfertas?: number;
  adjudicatarioNombre?: string;
  adjudicatarioNif?: string;
  importeAdjudicacion?: number;
  organoContratante?: OrganoContratante;
  updatedAt?: string;
}

export interface Documento {
  tipo: string;
  nombre: string;
  url: string;
  hash?: string;
}

export interface CriterioAdjudicacion {
  tipo: string;
  descripcion: string;
  peso?: number;
}

export interface LicitacionListResponse {
  'hydra:member': Licitacion[];
  'hydra:totalItems': number;
  'hydra:view'?: {
    'hydra:first'?: string;
    'hydra:last'?: string;
    'hydra:next'?: string;
    'hydra:previous'?: string;
  };
}

export interface FiltrosLicitacion {
  titulo?: string;
  tipoContrato?: string;
  estado?: string;
  provincia?: string;
  importeSinIva?: { gte?: number; lte?: number };
  fechaPublicacion?: { after?: string; before?: string };
  fechaLimitePresentacion?: { after?: string };
  codigosCpv?: string;
  page?: number;
  itemsPerPage?: number;
}
