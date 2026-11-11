# Como usar el Contrato de la API

Las instrucciones aquí se han hecho con Claude, si algo no funciona, decidme.

| Fichero | Para qué sirve |
|---|---|
| `openapi.yaml` | Contrato de la API en OpenAPI 3.1, escrito antes de programar (diseño). Sale del análisis funcional v3 y del diagrama E/R. |
| `index.html` | Swagger UI para ver el contrato y, más adelante, la documentación que genera Scramble. |

Todo el código va en inglés (rutas, claves JSON, enumeraciones, códigos de error); las descripciones, en castellano.

## Cómo verlo en Swagger

El navegador no deja abrir `openapi.yaml` desde `file://`, así que hay que servir la carpeta:

```bash
# desde la raíz del repositorio
python3 -m http.server 8081 -d docs/api
```

Y abrir <http://localhost:8081>.

Otras opciones:
- **PhpStorm:** abre `openapi.yaml` y usa la vista previa de Swagger.
- **Swagger Editor online:** pega el contenido en <https://editor.swagger.io> para editar con validación en tiempo real.

Para comprobar que el fichero es válido:

```bash
npx @redocly/cli lint docs/api/openapi.yaml
```

## Cómo se usa durante el desarrollo

1. **Antes de programar un endpoint**, se acuerda aquí (PR que cambia `openapi.yaml`, revisado por front end y back end).
2. **Front end sin esperar al back end:** un servidor falso que responde según el contrato:
   ```bash
   npx @stoplight/prism-cli mock docs/api/openapi.yaml   # http://127.0.0.1:4010
   ```
3. **Back end:** los Form Requests y los API Resources de Laravel deben producir exactamente estas formas
   (nombres de campos, enumeraciones y códigos de error).

## Paso a Scramble, endpoint a endpoint

Cuando un endpoint está implementado, Scramble lo documenta solo a partir del código, en `/docs/api`
(su propia interfaz) y `/docs/api.json` (la especificación OpenAPI).

En el mismo PR que implementa el endpoint:

1. Se comprueba que la documentación de Scramble coincide con lo que dice `openapi.yaml`.
2. Si coincide, **se elimina ese endpoint de `openapi.yaml`**. Si no coincide, o se corrige el código o se
   actualiza el contrato, pero nunca se deja de cualquier manera.

Así cada endpoint está siempre en un solo sitio: en `openapi.yaml` si aún no existe y en Scramble si ya
está programado. Al terminar el proyecto, `openapi.yaml` queda vacío y Scramble es la única especificación.

Para comparar las dos en Swagger UI, `index.html` tiene un desplegable con «Implementación (Scramble,
localhost)»; para que el navegador lo permita, hay que añadir `'docs/*'` a `paths` en `backend/config/cors.php`.

## Pendiente

- URL de producción del servidor.
