# PLACE - Licitaciones de Vehículos

Aplicación para consultar licitaciones públicas de vehículos desde la Plataforma de Contratación del Sector Público de España.

## Stack

- **Backend:** Symfony 7 + API Platform + PostgreSQL
- **Frontend:** Angular 21 (standalone components)
- **Puertos:** Backend 8500, Frontend 4500

## Funcionalidad

- Sincroniza licitaciones desde el feed ATOM de PLACE
- Muestra solo licitaciones relacionadas con vehículos (coches, motos, furgonetas, renting)
- Palabras clave: vehículo, coche, automóvil, motocicleta, furgoneta, furgón, flota de vehículos, alquiler de vehículos, parque móvil, renting

## Arrancar el proyecto

### Terminal 1 - Backend
```bash
cd backend
symfony server:start --port=8500
```

### Terminal 2 - Frontend
```bash
cd frontend
ng serve --port 4500
```

### Sincronizar licitaciones
```bash
cd backend
php bin/console app:sync-place
```

## Acceso

- **Frontend:** http://localhost:4500/licitaciones
- **API:** http://localhost:8500/api

## Prompt para replicar este proyecto con Claude

```
Crea una aplicación para consultar licitaciones públicas de España desde la Plataforma de Contratación del Sector Público (PLACE).

**Stack:**
- Backend: Symfony 7 + API Platform + PostgreSQL
- Frontend: Angular 21 (standalone components)
- Puertos: Backend 8500, Frontend 4500

**Funcionalidad:**
- Sincronizar licitaciones desde el feed ATOM de PLACE: https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilContratante_COMPLETO3.atom
- Mostrar solo licitaciones relacionadas con vehículos (coches, motos, furgonetas, renting)
- Palabras clave: vehículo, coche, automóvil, motocicleta, furgoneta, furgón, flota de vehículos, alquiler de vehículos, parque móvil, renting

**Entidades:**
- Licitacion: id, idPlace, expediente, titulo, descripcion, estado, tipoContrato, importeSinIva, importeConIva, provincia, codigosCpv, fechaPublicacion, fechaLimitePresentacion, urlLicitacion
- OrganoContratante: id, nif, nombre

**Comandos:**
- Sincronizar: php bin/console app:sync-place
- Backend: symfony server:start --port=8500
- Frontend: ng serve --port 4500
```

## Repositorio

https://github.com/labrax-soluciones/place-licitaciones
