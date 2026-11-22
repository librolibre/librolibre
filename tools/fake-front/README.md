# Front end de prueba (fake-front)

Páginas HTML sencillas que hacen de Angular para probar la API sin el front end real:
el login con Google completo y un panel con un botón por cada endpoint que ya funciona.
No forman parte de la aplicación: es una herramienta de desarrollo.

## Cómo arrancarlo

1. El back end en marcha, en `backend/`: `php artisan serve` (puerto **8000**).
   Su `.env` necesita `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` y `FRONTEND_URL=http://localhost:4200`.
2. Desde la raíz del repo:

   ```bash
   python3 -m http.server 4200 -d tools/fake-front --bind 127.0.0.1
   ```

3. Abrir http://localhost:4200 y pulsar «Entrar con Google».

Usa el puerto **4200**, el mismo que Angular, porque Laravel redirige ahí después de Google (`FRONTEND_URL`).
Por eso no puede estar en marcha a la vez que `ng serve`: para uno antes de arrancar el otro.

## Páginas

| Página | Qué hace |
|---|---|
| `/` | Botón «Entrar con Google» (un enlace a `/api/auth/google/redirect`) y los errores `?error=cancelled` / `?error=suspended` |
| `/auth/callback` | Canjea el `code` por el token (`POST /auth/token`), lo guarda en `localStorage`, llama a `GET /me` y comprueba que el código no sirve dos veces |
| `/panel/` | Un botón por endpoint: `GET /me`, `PATCH /me`, `DELETE /me`, privacidad, foto, logout, `GET /users/{id}` (con o sin token), idiomas y categorías. Muestra el estado y el JSON de cada respuesta |

## Notas

- `DELETE /me` anonimiza de verdad la cuenta en tu base de datos (pide confirmación).
- Para que las fotos se vean, ejecuta una vez `php artisan storage:link` en `backend/`.
- La página `/auth/callback` es una buena referencia para Angular: hace los mismos pasos que la guía `docs/frontend/conectar-con-la-api.md`.
