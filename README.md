# Inventory System

Sistema de gestión de inventarios construido con Laravel.

## Requisitos

- PHP 8.0+
- Composer
- MySQL
- Acceso a la línea de comandos (terminal)

## Instalación (entorno local)

1. Clona el repositorio:

```bash
git clone <https://github.com/angelODev26/inventory-system.git> inventory-system
cd inventory-system
```

2. Instala dependencias PHP:

```bash
composer install
```

3. Copia el archivo de entorno y genera la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configura `.env` con tus datos de base de datos y otros valores. Opcionalmente ajusta `APP_URL`.

5. Ejecuta migraciones y seeders (si deseas datos de prueba):

```bash
php artisan migrate
php artisan db:seed
```

6. Asegura permisos y crea el enlace de almacenamiento si es necesario:

```bash
php artisan storage:link
chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

7. Levanta el servidor de desarrollo:

```bash
php artisan serve
# Por defecto: http://127.0.0.1:8000
```

## Generar y ver la documentación API (Swagger / L5-Swagger)

Este proyecto usa `darkaonline/l5-swagger` + `zircote/swagger-php` para generar la documentación OpenAPI basada en anotaciones `@OA`.

1. Publica los recursos (si no se ha hecho):

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

2. Genera la documentación JSON/YAML:

```bash
php artisan l5-swagger:generate
```

3. Abre la interfaz Swagger UI en el navegador (ruta por defecto):

```
http://127.0.0.1:8000/api/documentation
```

Notas:
- Si `php artisan l5-swagger:generate` muestra advertencias sobre `@OA\Info()` o `@OA\PathItem()`, asegúrate de que existen anotaciones `@OA\Info` y que `config/l5-swagger.php` apunta a las carpetas correctas (por defecto `app/`).
- También puedes generar con el binario de `swagger-php` directamente:

```bash
./vendor/bin/openapi --bootstrap vendor/autoload.php app -o storage/api-docs/api-docs.yaml
```

## Comandos útiles

- Instalar dependencias: `composer install`
- Regenerar autoload: `composer dump-autoload`
- Limpiar cache de configuración: `php artisan config:clear`
- Generar docs: `php artisan l5-swagger:generate`
- Levantar servidor: `php artisan serve`

## Troubleshooting (problemas comunes)

- Errores de permisos: ejecutar `chown`/`chmod` en `storage` y `bootstrap/cache`.
- Variables de entorno: verifica que `.env` tenga las credenciales correctas.
- Extensiones de PHP faltantes: revisa `php -m` y añade las extensiones necesarias.
- Si L5-Swagger no encuentra `@OA\Info()`: crea un archivo con las anotaciones globales (por ejemplo `app/Swagger/Swagger.php`) o ajusta `config/l5-swagger.php` para incluir la ruta donde están tus anotaciones.

## Dónde consultar la documentación una vez instalada

- Swagger UI (interactivo): `http://{tu-host}:{puerto}/api/documentation`
- Archivo JSON generado (por defecto): `storage/api-docs/api-docs.json`
- Archivo YAML generado (si activado): `storage/api-docs/api-docs.yaml`

---
