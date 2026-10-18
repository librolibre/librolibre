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
    Definición de Ideas Proyecto    :done, 2026-09-29, 2026-10-12
    Hito - Presentación Idea        :milestone, 2026-10-09, 0d
    Elaboración Documento Proyecto  :done, 2026-10-12, 2026-10-31
    Hito - Entrega Documento        :milestone, 2026-10-30, 0d

    section Fase 2: Análisis y Diseño
    Modelo E/R                      :2026-10-31, 2026-11-14
    Wireframes y UI Figma (Front)   :2026-10-31, 2026-11-14
    Diseño API REST (Back)          :2026-10-31, 2026-11-14
    Prototipado HTML/CSS (Front)    :2026-10-31, 2026-11-14

    section Fase 3: Desarrollo
    Pantalla login y Auth           :2026-11-15, 2026-11-28
    Panel Admin                     :2026-11-15, 2026-11-28
    Panel Admin (Usuarios)          :2026-11-15, 2026-11-28
    Panel Admin (Libros?)           :2026-11-15, 2026-11-28
    Pantalla principal              :2026-11-28, 2026-12-12
    Panel de Usuario (General)      :2026-11-28, 2026-12-12
    Subir Libro y ficha de libro    :2026-11-28, 2026-12-12
    Autocompletado ISBN             :2026-11-28, 2026-12-12
    Panel de Usuario (mis libros)   :2026-12-12, 2026-12-26
    Buscador de Libros              :2026-12-12, 2026-12-26
    Trueque de libros               :2026-12-12, 2026-12-26
    Chat                            :2026-12-12, 2026-12-26
    Seguimiento de intercambios     :2026-12-12, 2026-12-26
    Reputación y seguridad          :2026-12-12, 2026-12-26
    Reportar usuario                :2026-12-12, 2026-12-26
    Panel Admin (Usuarios report)   :2026-12-12, 2026-12-26

    section Fase 4: Cierre y Defensa
    Testing y Despliegue           :2026-12-26, 2027-01-02
    Memoria y Diapositivas         :2027-01-03, 2027-01-12
    Ensayos Defensa                :2027-01-13, 2027-01-14
    Hito: Defensa Oral             :milestone, 2027-01-15, 0d
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
