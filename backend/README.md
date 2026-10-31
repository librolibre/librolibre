# LibroLibre · Back end (API Laravel)

API REST de LibroLibre construida con **Laravel 13**, **Sanctum** (tokens), **Scramble** (documentación OpenAPI) y **PostgreSQL 18**. En local se ejecuta con **Laravel Sail** (Docker), así que no hace falta instalar PostgreSQL en tu equipo.

## Requisitos

| Herramienta | Versión | Para qué |
|---|---|---|
| Docker | Docker Desktop, OrbStack o Docker Engine | Ejecutar Sail (PHP 8.5 + PostgreSQL 18) |
| Git | cualquiera reciente | Clonar el repositorio |
| PHP + Composer | PHP ≥ 8.4 *(opcional)* | Solo para el primer `composer install`; hay alternativa sin PHP más abajo |

## Instalación (primera vez)

Todos los comandos se ejecutan desde la carpeta `backend/`.

```bash
git clone https://github.com/librolibre/librolibre.git
cd librolibre/backend
cp .env.example .env
```

Instala las dependencias. **Con PHP en tu equipo:**

```bash
composer install
```

**Sin PHP en tu equipo** (usa un contenedor temporal):

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php85-composer:latest composer install --ignore-platform-reqs
```

Arranca los contenedores y prepara la aplicación:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

La primera vez `sail up` tarda varios minutos porque construye la imagen.

### Comprobar que funciona

| URL | Resultado esperado |
|---|---|
| http://localhost/api/health | `{"status":"ok"}` |
| http://localhost/docs/api | Documentación de la API (Scramble) |

## Uso diario

```bash
./vendor/bin/sail up -d        # arrancar
./vendor/bin/sail artisan test # tests
./vendor/bin/sail stop         # parar (los datos de la BD se conservan)
```

**Opcional:** para no escribir `./vendor/bin/sail` cada vez, puedes añadir este alias a tu `~/.zshrc` o `~/.bashrc`. Si no lo usas, sigue escribiendo `./vendor/bin/sail` como en los ejemplos.

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

## Notas por sistema operativo

### macOS
- Instala Docker Desktop u OrbStack y ábrelo antes de `sail up`.
- PHP es opcional; si lo quieres: `brew install php composer`.

### Ubuntu
- Instala Docker Engine y el plugin de Compose siguiendo la guía oficial de Docker.
- Añade tu usuario al grupo `docker` para no usar `sudo`, y cierra sesión para aplicarlo:
  ```bash
  sudo usermod -aG docker $USER
  ```

### Windows 10 / 11 (WSL2 obligatorio)
- Instala WSL2 con Ubuntu (`wsl --install` en PowerShell como administrador) y Docker Desktop con la integración WSL2 activada para esa distribución.
- **Clona el repositorio dentro de WSL** (por ejemplo en `~/proyectos`), **no** en `C:\` ni en `/mnt/c/...`: el rendimiento es muy malo y los permisos fallan.
- Ejecuta todos los comandos desde la terminal de Ubuntu (WSL). Puedes abrir el proyecto con `code .` desde WSL.

## Problemas frecuentes

- **El puerto 80 o 5432 ya está en uso** (otro servidor web, XAMPP, otro PostgreSQL u otro proyecto Docker): cambia los puertos en tu `.env` y reinicia Sail.
  ```dotenv
  APP_PORT=8080
  FORWARD_DB_PORT=54320
  ```
  Si cambias `APP_PORT`, la API pasa a estar en `http://localhost:8080`.
- **Error de autenticación con PostgreSQL** después de cambiar `DB_DATABASE`, `DB_USERNAME` o `DB_PASSWORD`: el volumen de la base de datos se creó con los valores anteriores. Bórralo y vuelve a crearlo (**se pierden los datos locales**):
  ```bash
  ./vendor/bin/sail down -v
  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan migrate
  ```

## Notas

- **Nunca subas el archivo `.env`** ni cadenas de conexión de Neon. `.env.example` solo contiene valores por defecto de Sail.
- Los tests usan la base de datos `testing`, que Sail crea automáticamente.
