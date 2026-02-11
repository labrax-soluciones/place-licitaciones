# PLACE - Licitaciones del Sector Público

Aplicación para consultar y monitorizar licitaciones públicas desde la Plataforma de Contratación del Sector Público de España (PLACE).

## Cómo se conecta a las licitaciones del Estado

### Fuente de datos

PLACE **no ofrece una API REST ni webservice**. Los datos se obtienen de un **feed ATOM (XML) público** que no requiere autenticación:

```
https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom
```

Este feed se actualiza periódicamente con las licitaciones más recientes publicadas en la plataforma.

### Qué contiene cada entrada del feed

Cada `<entry>` del ATOM incluye datos estructurados en múltiples namespaces XML:

| Campo | Descripción |
|-------|-------------|
| Expediente | Número identificador único |
| Título | Descripción de la licitación |
| Estado | Publicada, En evaluación, Adjudicada, Resuelta, etc. |
| Tipo de contrato | Suministros, Servicios, Obras |
| Importes | Sin IVA y con IVA |
| Códigos CPV | Clasificación de productos/servicios |
| Fechas | Publicación y límite de presentación |
| Órgano contratante | NIF, nombre, dirección, contacto, código DIR3 |
| Provincia | Ubicación geográfica |
| Documentos | Enlaces a pliegos y documentación |
| Criterios de adjudicación | Criterios y ponderaciones |
| Datos de adjudicación | Adjudicatario, importe, fecha (si está resuelta) |

### Namespaces XML utilizados

El feed usa múltiples namespaces que hay que registrar para poder hacer consultas XPath:

- **Atom:** `http://www.w3.org/2005/Atom`
- **CBC:** Common Basic Components
- **CAC:** Common Aggregate Components
- **Extensiones PLACE:** namespaces propios de la plataforma

### Pipeline de datos

```
┌──────────────────────────────────────────────────┐
│  PLACE - Feed ATOM público (XML)                 │
│  contrataciondelestado.es/sindicacion/...        │
└──────────────────────┬───────────────────────────┘
                       │ HTTP GET (120s timeout)
                       ▼
┌──────────────────────────────────────────────────┐
│  PlaceAtomParser (PHP)                           │
│  - SimpleXML + XPath con 4+ namespaces           │
│  - Extrae 40+ campos por licitación              │
│  - Cache en memoria para órganos contratantes    │
│  - Flush cada 50 registros (optimización)        │
└──────────────────────┬───────────────────────────┘
                       │ Doctrine ORM (upsert por idPlace)
                       ▼
┌──────────────────────────────────────────────────┐
│  PostgreSQL                                      │
│  Tablas: licitacion, organo_contratante, alerta  │
│  Índices: expediente, estado, tipo, fechas,      │
│           provincia, importe                     │
└──────────────────────┬───────────────────────────┘
                       │
          ┌────────────┴────────────┐
          ▼                         ▼
┌──────────────────┐    ┌──────────────────────┐
│  API Platform    │    │  Controllers custom  │
│  CRUD automático │    │  /dashboard/*        │
│  /licitacions    │    │  /categorias         │
│  /organo_contr.. │    │  /licitaciones/cat.. │
│  /alertas        │    └──────────────────────┘
└────────┬─────────┘
         │ JSON / JSON-LD
         ▼
┌──────────────────────────────────────────────────┐
│  Angular Frontend                                │
│  Dashboard, listado, detalle, filtros, alertas   │
└──────────────────────────────────────────────────┘
```

## Cómo replicar el proceso

### 1. Requisitos previos

- PHP 8.2+
- Composer
- PostgreSQL 16
- Node.js 20+ y npm
- Symfony CLI (opcional pero recomendado)

### 2. Variables de entorno

```env
DATABASE_URL=postgresql://user:password@localhost:5432/place_licitaciones
PLACE_ATOM_URL=https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$
```

### 3. Backend (Symfony + API Platform)

```bash
cd backend

# Instalar dependencias
composer install

# Crear base de datos y ejecutar migraciones
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Sincronizar licitaciones desde PLACE
php bin/console app:sync-place

# Iniciar servidor
symfony server:start --port=8500
```

### 4. Frontend (Angular)

```bash
cd frontend

# Instalar dependencias
npm install

# Iniciar servidor de desarrollo
ng serve --port 4500
```

### 5. Docker (alternativa)

```bash
docker-compose up
# Backend:  http://localhost:8000
# Frontend: http://localhost:4200
# Database: localhost:5432
```

## Estructura del proyecto

```
PLACE/
├── backend/                          # Symfony 7.4
│   ├── src/
│   │   ├── Command/
│   │   │   └── SyncPlaceCommand.php          # Comando de sincronización
│   │   ├── Controller/
│   │   │   └── DashboardController.php       # Endpoints custom (stats, categorías)
│   │   ├── Entity/
│   │   │   ├── Licitacion.php                # Entidad principal (25+ campos)
│   │   │   ├── OrganoContratante.php         # Órganos contratantes
│   │   │   ├── Alerta.php                    # Alertas de usuario
│   │   │   └── Usuario.php                   # Usuarios (base para auth futuro)
│   │   ├── Repository/
│   │   │   ├── LicitacionRepository.php      # Queries avanzadas y filtros
│   │   │   └── OrganoContratanteRepository.php
│   │   └── Service/
│   │       └── PlaceAtomParser.php           # Parseo del feed ATOM/XML
│   └── config/
│
└── frontend/                         # Angular 21
    └── src/app/
        ├── components/
        │   ├── lista-licitaciones/            # Listado con filtros
        │   ├── detalle-licitacion/            # Vista detalle
        │   ├── dashboard/                     # Estadísticas
        │   ├── alertas/                       # Gestión de alertas
        │   └── header/                        # Navegación
        ├── services/
        │   └── api.service.ts                 # Cliente HTTP
        └── models/
            └── licitacion.model.ts            # Interfaces TypeScript
```

## Endpoints de la API

### API Platform (CRUD automático)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/licitacions` | Listado con filtros (search, range, date, order) |
| GET | `/api/licitacions/{id}` | Detalle de una licitación |
| GET | `/api/organo_contratantes` | Órganos contratantes |
| GET/POST/PATCH/DELETE | `/api/alertas` | Gestión de alertas |

### Controllers custom

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/dashboard/estadisticas` | Estadísticas generales |
| GET | `/api/dashboard/recientes` | Últimas 10 licitaciones |
| GET | `/api/dashboard/abiertas` | Licitaciones abiertas con días restantes |
| GET | `/api/categorias` | Categorías disponibles |
| GET | `/api/licitaciones/categoria/{cat}` | Filtrar por categoría |

## Sistema de categorías

Las licitaciones se clasifican por palabras clave (búsqueda en título y descripción):

| Categoría | Palabras clave |
|-----------|---------------|
| **Vehículos** | vehículo, coche, automóvil, motocicleta, furgoneta, furgón, flota de vehículos, alquiler de vehículos, parque móvil, renting |
| **Informática** | informático, software, hardware, ordenador, servidor, cloud, nube, desarrollo web, aplicación |
| **Limpieza** | limpieza, higiene, desinfección, mantenimiento limpieza, servicio limpieza |
| **Seguridad** | seguridad, vigilancia, vigilante, alarma, cctv, videovigilancia |

## Claves técnicas para replicar

1. **No hay API oficial** — todo se basa en consumir el feed ATOM/XML público
2. **El parseo XML es lo más complejo** — hay que manejar múltiples namespaces con XPath
3. **Se usa SimpleXML nativo de PHP** — sin librerías externas para el parseo
4. **Patrón upsert** — se busca por `idPlace` (URL única) para evitar duplicados al re-sincronizar
5. **Cache en memoria** — los órganos contratantes se cachean durante la sincronización para evitar consultas repetidas a la BD
6. **Flush por lotes** — cada 50 registros para no saturar la memoria
7. **Se guarda el XML crudo** — en cada licitación, para auditoría y depuración

## URLs de desarrollo

- **Frontend:** http://localhost:4500
- **API:** http://localhost:8500/api
- **Documentación API:** http://localhost:8500/api/docs
- **Vehículos:** http://localhost:8500/api/licitaciones/categoria/vehiculos

## Stack técnico

| Componente | Tecnología |
|------------|------------|
| Backend | PHP 8.2 / Symfony 7.4 / API Platform |
| Base de datos | PostgreSQL 16 / Doctrine ORM |
| Frontend | Angular 21 (standalone components) / TypeScript 5.9 |
| Parseo XML | SimpleXML + XPath (PHP nativo) |
| Gráficas | Chart.js + ng2-charts |
| Contenedores | Docker + Docker Compose |
