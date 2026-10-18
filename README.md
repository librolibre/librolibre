# LibroLibre

Aplicación web para intercambiar y donar libros entre vecinos. Proyecto final del ciclo DAW.

- **Front end:** Angular (`frontend/`)
- **Back end:** Laravel API REST (`backend/`)
- **Base de datos:** MySQL
- **Documentación:** `docs/`


## Diagrama Gantt Proyecto

```mermaid
gantt
    title Planificación del Proyecto - Intercambio de Libros
    dateFormat  YYYY-MM-DD
    axisFormat %d/%m
    
    section Fase 1: Definición
    Presentación Curso              :milestone, 2026-09-29, 0d
    Definición de Ideas Proyecto    :done, 2026-09-03, 2026-10-12
    Hito - Presentación Idea        :milestone, 2026-10-09, 0d
    Elaboración Propuesta Técnica   :done, 2025-10-12, 2025-10-24
    Hito - Entrega Propuesta        :milestone, 2025-10-23, 0d

    section Fase 2: Análisis y Diseño
    Modelo E/R y Casos de Uso (Back) :active, 2025-10-25, 2025-11-07
    Wireframes y UI Figma (Front)   :active, 2025-10-25, 2025-11-07
    Diseño API REST (Back)          :2025-11-08, 2025-11-14
    Prototipado HTML/CSS (Front)    :2025-11-08, 2025-11-14

    section Fase 3: Desarrollo
    Sprint 1 - Auth + ISBN (Back)   :2025-11-15, 2025-11-28
    Sprint 1 - UI Base + Escáner    :2025-11-15, 2025-11-28
    Sprint 2 - Trueque + Filtros    :2025-11-29, 2025-12-12
    Sprint 2 - UI Trueques + Chat   :2025-11-29, 2025-12-12
    Integración y Ajustes          :2025-12-13, 2025-12-19

    section Fase 4: Cierre y Defensa
    Testing y Despliegue           :2025-12-20, 2026-01-02
    Memoria y Diapositivas         :2026-01-03, 2026-01-12
    Ensayos Defensa                :2026-01-13, 2026-01-14
    Hito: Defensa Oral             :milestone, 2026-01-15, 0d
```


## Estructura

```
librolibre/
├── backend/          Laravel (API REST)
├── frontend/         Angular
├── docs/             Diagramas, análisis, contrato de la API
└── .github/          Plantillas de PR y GitHub Actions
```

## Puesta en marcha (local)

### Back end

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve          # http://localhost:8000
```

Con Docker (Laravel Sail): `./vendor/bin/sail up -d` en lugar de `php artisan serve`.

### Front end

```bash
cd frontend
npm install
npx ng serve               # http://localhost:4200
```

## Flujo de trabajo con Git

- `main`: solo código estable (entregas).
- `develop`: rama de integración; las funcionalidades terminadas se fusionan aquí.
- Una rama por tarjeta de Trello, creada desde `develop`:
  - `feature/<descripcion-corta>` — funcionalidad nueva
  - `fix/<descripcion-corta>` — corrección de errores
  - `docs/<descripcion-corta>` — documentación
- Todo cambio entra por **pull request** a `develop`, con al menos **una revisión** y los tests en verde.

```bash
git checkout develop
git pull
git checkout -b feature/busqueda-por-isbn
# ... trabajar, hacer commits ...
git push -u origin feature/busqueda-por-isbn
# abrir el pull request en GitHub hacia develop
```

### Mensajes de commit

Formato corto en presente, con prefijo:

- `feat: añade búsqueda por ISBN`
- `fix: corrige filtro de distancia`
- `docs: actualiza diagrama ER`
- `test: añade tests del flujo de trueque`

## Importante

Nunca subas el archivo `.env`: contiene contraseñas y claves. Usa `.env.example` con valores ficticios.
