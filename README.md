# PLACE - Licitaciones de Vehículos

Aplicación para consultar licitaciones públicas de vehículos desde la Plataforma de Contratación del Sector Público de España.

## Cómo funciona

### Conexión con PLACE

No usamos un webservice ni API REST. PLACE expone sus datos mediante **feeds ATOM (XML)** públicos que se actualizan periódicamente.

**Feed utilizado:**
```
https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilContratante_COMPLETO3.atom
```

Este feed contiene las licitaciones más recientes en formato ATOM/XML. Cada entrada (`<entry>`) incluye:
- Título y descripción de la licitación
- Expediente
- Estado (Publicada, En evaluación, Adjudicada, etc.)
- Tipo de contrato (Suministros, Servicios, Obras)
- Importes (sin IVA y con IVA)
- Fechas (publicación, límite de presentación)
- Códigos CPV (clasificación de productos/servicios)
- Órgano contratante (NIF y nombre)
- Provincia
- URL a la licitación en PLACE

### Proceso de sincronización

1. El comando `php bin/console app:sync-place` descarga el feed ATOM
2. Parsea el XML y extrae los datos de cada licitación
3. Guarda/actualiza en PostgreSQL usando Doctrine ORM
4. Evita duplicados usando el `idPlace` (URL única de cada licitación)

### Filtrado por categoría

La app filtra licitaciones de vehículos buscando palabras clave en título y descripción:
- vehículo, coche, automóvil, motocicleta, furgoneta, furgón
- flota de vehículos, alquiler de vehículos, parque móvil, renting

## Stack técnico

| Componente | Tecnología |
|------------|------------|
| Backend | Symfony 7 + API Platform |
| Base de datos | PostgreSQL |
| Frontend | Angular 21 (standalone components) |
| Parseo XML | SimpleXML (PHP nativo) |
| Puerto backend | 8500 |
| Puerto frontend | 4500 |

## Estructura del proyecto

```
PLACE/
├── backend/                    # Symfony 7
│   ├── src/
│   │   ├── Entity/
│   │   │   ├── Licitacion.php
│   │   │   └── OrganoContratante.php
│   │   ├── Repository/
│   │   │   └── LicitacionRepository.php  # Consultas y filtro por categoría
│   │   ├── Service/
│   │   │   └── PlaceAtomParser.php       # Parsea el feed ATOM
│   │   ├── Command/
│   │   │   └── SyncPlaceCommand.php      # Comando de sincronización
│   │   └── Controller/
│   │       └── DashboardController.php   # Endpoints de categorías
│   └── config/
│
└── frontend/                   # Angular 21
    └── src/app/
        ├── components/
        │   └── licitaciones/
        │       └── lista-licitaciones.component.ts
        ├── services/
        │   └── api.service.ts
        └── models/
            └── licitacion.model.ts
```

## Comandos

```bash
# Terminal 1 - Backend
cd backend
symfony server:start --port=8500

# Terminal 2 - Frontend
cd frontend
ng serve --port 4500

# Sincronizar licitaciones desde PLACE
cd backend
php bin/console app:sync-place
```

## URLs

- **Frontend:** http://localhost:4500/licitaciones
- **API:** http://localhost:8500/api
- **Endpoint vehículos:** http://localhost:8500/api/licitaciones/categoria/vehiculos

## Repositorio

https://github.com/labrax-soluciones/place-licitaciones
