# LibroLibre · Back end (API Laravel)

API REST de LibroLibre construida con **Laravel 13**, **Sanctum** (tokens), **Scramble** (documentación OpenAPI) y **PostgreSQL 18**. En local se trabaja con PHP y PostgreSQL instalados en tu equipo.

## Requisitos

| Herramienta | Versión | Para qué |
|---|---|---|
| PHP | ≥ 8.4 con la extensión `pdo_pgsql` | Ejecutar Laravel |
| Composer | 2.x | Instalar dependencias |
| PostgreSQL | 18 | Base de datos local (desarrollo y tests) |
| Git | cualquiera reciente | Clonar el repositorio |

## Bases de datos: local y Neon

> ⚠️ **Para trabajar, usa siempre tu PostgreSQL local.** Neon (`dev` y `production`) es la base de datos de los entornos desplegados, no la de tu rama.

| Base de datos | Dónde | Para qué |
|---|---|---|
| `librolibre` | Tu equipo (`127.0.0.1`) | Desarrollo: `php artisan serve`, `migrate:fresh --seed` |
| `testing` | Tu equipo (`127.0.0.1`) | `php artisan test` (los tests la vacían cada vez) |
| Neon `dev` | Neon | Entorno de desarrollo desplegado. Solo se migra desde `develop` |
| Neon `production` | Neon | Producción (Heroku la migra en cada despliegue) |

Reglas:
- **Nunca** pongas los datos de conexión de Neon en `.env` ni en `.env.testing` para trabajar en una rama.
- **Nunca** ejecutes `migrate:fresh`, `migrate:reset`, `migrate:rollback` ni `db:wipe` contra Neon: borran datos.
- Una migración que ya se ha ejecutado en Neon **no se cambia, no se renombra y no se borra**: se crea una migración nueva.
- El esquema de la base de datos son las **migraciones** (`database/migrations`); no se cambian tablas a mano en Neon.

## Instalación (primera vez)

Todos los comandos se ejecutan desde la carpeta `backend/`.

```bash
git clone https://github.com/librolibre/librolibre.git
cd librolibre/backend
cp .env.example .env
cp .env.testing.example .env.testing
composer install
```

Crea las dos bases de datos locales:

```bash
createdb librolibre
createdb testing
```

Edita `.env` y `.env.testing`: pon tu usuario de PostgreSQL en `DB_USERNAME` (y `DB_PASSWORD` si tiene). Después:

```bash
php artisan key:generate
php artisan key:generate --env=testing
php artisan migrate --seed
php artisan test
```

### Comprobar que funciona

```bash
php artisan serve
```

| URL | Resultado esperado |
|---|---|
| http://localhost:8000/api/health | `{"status":"ok"}` |
| http://localhost:8000/docs/api | Documentación de la API (Scramble) |

## Uso diario

```bash
php artisan serve                 # arrancar la API en http://localhost:8000
php artisan test                  # tests
php artisan migrate:fresh --seed  # rehacer la base de datos LOCAL desde cero
```

## Notas por sistema operativo

### macOS
```bash
brew install php composer postgresql@18
brew services start postgresql@18
echo 'export PATH="/opt/homebrew/opt/postgresql@18/bin:$PATH"' >> ~/.zshrc
```
Con Homebrew, el usuario de PostgreSQL es tu usuario de macOS y no tiene contraseña.

### Ubuntu
- Instala PHP 8.4+ con `php-pgsql`, Composer y PostgreSQL 18 (repositorio oficial de PostgreSQL).
- Crea un usuario de PostgreSQL con tu nombre de usuario: `sudo -u postgres createuser --superuser $USER`.

### Windows 10 / 11 (WSL2 obligatorio)
- Instala WSL2 con Ubuntu (`wsl --install` en PowerShell como administrador) y sigue los pasos de Ubuntu **dentro de WSL**.
- **Clona el repositorio dentro de WSL** (por ejemplo en `~/proyectos`), **no** en `C:\` ni en `/mnt/c/...`.

## Problemas frecuentes

- **`could not find driver`**: falta la extensión `pdo_pgsql` de PHP. Comprueba con `php -m | grep pgsql`.
- **`database "librolibre" does not exist`**: falta `createdb librolibre` (o `createdb testing` para los tests).
- **`No application encryption key has been specified`** en los tests: falta `php artisan key:generate --env=testing`.
- **`relation "…" already exists`** al migrar en local: tu base de datos tiene tablas de una versión anterior. Ejecuta `php artisan migrate:fresh --seed` (solo en local).

## Notas

- **Nunca subas `.env` ni `.env.testing`** ni cadenas de conexión de Neon. Los ficheros `.example` solo tienen valores de ejemplo.
- Los tests usan la base de datos local `testing`; `phpunit.xml` lo fuerza aunque `.env` apunte a otra.
