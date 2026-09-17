# BPX Labs · Costes y rentabilidad

Aplicación local para gestionar productos, registrar sus costes unitarios y comparar el coste con el precio de venta mediante un gráfico y una tabla de rentabilidad.

**Estado actual:** productos con precio de venta y color personalizable, tipos de coste, costes por producto e informe de beneficio y margen. Symfony 7.4, PHP 8.3, Doctrine ORM, EasyAdmin 4 y MariaDB 10.11.

## 1. Preparar otro ordenador

La opción recomendada es **Docker**: incluye PHP, Composer y MariaDB; no necesitas instalarlos por separado. Necesitas Git, Docker con Docker Compose v2 y acceso a Internet durante la primera instalación. Arranca Docker Desktop o el servicio Docker antes de continuar.

Los comandos siguientes están preparados para una terminal de Linux, macOS o WSL2 en Windows, desde la carpeta del proyecto. Comprueba que Docker está disponible:

```bash
git --version
docker info
docker compose version
```

Descarga el repositorio y crea la configuración local:

```bash
git clone https://github.com/juanfegc/bpxlabs_rentabilidad.git
cd bpxlabs_rentabilidad
cp .env.example .env
```

También puedes clonar por SSH si ya tienes tu clave configurada en GitHub: `git clone git@github.com:juanfegc/bpxlabs_rentabilidad.git`.

El archivo `.env.example` contiene valores de desarrollo. `.env`, `.env.dev` y `.env.local` no se suben a Git. Para esta instalación local puedes usar los valores del ejemplo; no son una configuración de producción.

Por defecto, la aplicación usa `http://localhost:8000/admin` y MariaDB se publica en `127.0.0.1:3307`. Si esos puertos están ocupados, cambia `APP_PORT` o `DATABASE_PORT` en `.env` antes de arrancar. Si cambias `APP_PORT`, actualiza también `DEFAULT_URI` y usa ese puerto en el navegador.

## 2. Primera instalación con Docker

Ejecuta estos comandos en orden. Si alguno falla, resuelve el error antes de continuar:

```bash
# Construir la imagen con PHP 8.3, sus extensiones y Composer.
docker compose build

# Crear y arrancar MariaDB con un volumen de datos propio.
docker compose up -d database

# Instalar las versiones de composer.lock y preparar Symfony y sus recursos.
docker compose run --rm app composer install --no-interaction

# Crear las tablas y aplicar todas las migraciones pendientes.
docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction

# Añadir los nueve tipos de coste iniciales, conservando los datos existentes.
docker compose run --rm app php bin/console doctrine:fixtures:load --append --no-interaction

# Arrancar el servidor web.
docker compose up -d app
```

Abre **http://localhost:8000/admin**. No se necesita usuario ni contraseña. Una instalación nueva empieza sin productos ni costes de producto; solo se cargan los tipos de coste iniciales.

No hace falta ejecutar npm, compilar un frontend ni instalar Symfony CLI. `composer install` instala los recursos de EasyAdmin y las dependencias del importmap.

Comprueba que los contenedores y la aplicación están listos:

```bash
docker compose ps
docker compose exec -T app php bin/console doctrine:schema:validate
docker compose exec -T app vendor/bin/phpunit
```

La base de datos debe aparecer como `healthy`, el esquema debe estar sincronizado y la suite actual debe pasar todas las pruebas. Las pruebas del informe usan SQLite en memoria, incluido en la imagen Docker, y no modifican tus productos de MariaDB.

## 3. Usar la aplicación

1. En **Productos**, crea un producto con nombre, PVP con IVA del 10 % y color. El color inicial es aleatorio; pulsa el cuadro para elegir uno específico. El PVU sin IVA se calcula automáticamente (PVP / 1,10) y se utiliza en el informe. Puedes dejar el precio vacío si aún no lo conoces.
2. En **Costes de producto**, añade una línea por cada coste unitario, eligiendo producto y tipo de coste. Puedes registrar varias líneas del mismo tipo.
3. Vuelve a **Inicio** para consultar el gráfico y la tabla. Los resultados se recalculan al cargar la página.
4. Desde la tabla puedes **Editar producto** o **Ver costes** filtrados por ese producto. En **Tipos de coste** puedes gestionar las categorías.

Ejemplo: PVP `110` (PVU sin IVA `100`), una línea de coste de `40` y otra de `20`. En Inicio verás coste total `60`, beneficio por unidad `40` y margen `40 %`.

### Importes y colores

- El **PVP se introduce con IVA del 10 % incluido**; el PVU se calcula automáticamente con cuatro decimales. Los costes y los importes del informe son **euros por unidad, sin IVA**. Escribe los decimales con punto, por ejemplo `12.3456`: se admiten hasta ocho cifras enteras y cuatro decimales, sin valores negativos.
- Los nombres de producto y de tipo de coste son obligatorios y únicos dentro de cada catálogo, con un máximo de 160 caracteres.
- El precio cero es un precio registrado; un precio vacío significa que está pendiente.
- El gráfico usa una escala común para todos los productos. La barra superior representa el coste, con el color del producto aclarado y rayado; la inferior representa la venta, con el color sólido.
- El informe muestra importes con cuatro decimales y porcentajes con dos. Resume cuántos productos tienen beneficio, pérdidas, equilibrio o datos pendientes.

### Cálculos y datos pendientes

- **Coste total unitario:** suma de todas las líneas del producto.
- **Beneficio unitario:** precio de venta − coste total, redondeado a cuatro decimales.
- **Margen sobre venta:** `(precio − coste) / precio × 100`. Puede ser negativo si hay pérdidas.
- Sin precio o sin líneas de coste, el producto queda pendiente y no se muestra beneficio ni margen. Una línea explícita de coste cero sí cuenta como coste registrado.
- Con precio cero y al menos una línea de coste, se calcula el beneficio, pero el margen se muestra como `—` para evitar dividir entre cero.
- El informe refleja los costes que hayas registrado, no las ventas totales ni otros gastos que no hayas introducido.

Los tipos iniciales son Materia prima, Envase, Etiqueta, Sleeve, Dosificador, Fabricación, Transporte, Comisión y Otros. La carga con `--append` puede repetirse sin duplicarlos. **No omitas `--append`: la carga de fixtures sin esa opción purga los datos.**

Al borrar un producto se borran también sus líneas de coste. Un tipo de coste que esté en uso no se puede borrar.

El servicio PHP `App\Pricing\MarginCalculator` también calcula markup sobre coste y precio objetivo a partir de un margen; estas dos funciones aún no tienen controles en la interfaz. El precio objetivo requiere coste positivo y margen entre 0 (incluido) y 100 (excluido). El servicio rechaza valores negativos o no finitos y las divisiones entre cero; sus resultados no se redondean hasta que los usa el informe.

Esta iteración no incluye autenticación, canales de venta, inventario, registro de ventas ni gestión fiscal del IVA. Está preparada para uso local; el servidor de desarrollo no es un despliegue de producción.

## 4. Parar y volver a arrancar

Para detener los contenedores y conservar los datos:

```bash
docker compose down
```

Para volver a trabajar después de la primera instalación:

```bash
docker compose up -d
```

Los datos están en el volumen `database_data` del proyecto Compose. **No uses `docker compose down -v` si quieres conservarlos:** la opción `-v` elimina el volumen. Usa la misma carpeta de instalación para volver a acceder al mismo proyecto Compose y su volumen.

## 5. Actualizar una instalación existente

Desde la carpeta donde ya tienes el proyecto, con tus cambios locales guardados y una copia de los datos si los necesitas:

```bash
git pull --ff-only
# Detener el servidor mientras se actualizan dependencias y esquema.
docker compose stop app
docker compose build
docker compose up -d database
docker compose run --rm app composer install --no-interaction
docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction
docker compose run --rm app php bin/console doctrine:fixtures:load --append --no-interaction
docker compose up -d app
```

No vuelvas a copiar `.env.example` encima de tu `.env`: conserva tu configuración y añade únicamente las variables nuevas que necesites. Las migraciones actuales conservan los productos; al actualizar desde la primera iteración, los precios nuevos quedan vacíos y se asigna un color inicial a cada producto.

## 6. Llevar también tus datos a otro ordenador

**GitHub guarda el código, no la base de datos.** Clonar el repositorio reproduce la aplicación, pero no copia los productos, precios ni costes que hayas introducido. Para llevarlos contigo, exporta una copia desde el ordenador de origen con MariaDB arrancada:

```bash
mkdir -p backups
docker compose exec -T database sh -c 'exec mariadb-dump --single-transaction --user="$MARIADB_USER" --password="$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > backups/rentabilidad.sql
```

Transfiere ese archivo al otro ordenador por un medio privado. La carpeta `backups/` está excluida de Git.

En el ordenador de destino, completa primero la instalación y guarda el archivo en `backups/rentabilidad.sql`. Restaura solo si quieres sustituir el contenido de las tablas de destino por el de la copia:

```bash
docker compose stop app
docker compose exec -T database sh -c 'exec mariadb --user="$MARIADB_USER" --password="$MARIADB_PASSWORD" "$MARIADB_DATABASE"' < backups/rentabilidad.sql
docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction
docker compose run --rm app php bin/console doctrine:fixtures:load --append --no-interaction
docker compose up -d app
```

La copia incluye el historial de migraciones; se aplicarán las que falten si el código de destino es más reciente. Usa la misma versión del código o una posterior a la del origen.

## 7. Alternativa sin Docker

Esta opción requiere preparar manualmente PHP **8.3.x** (el proyecto no admite PHP 8.4 en `composer.json`), Composer 2 y MariaDB 10.11. PHP necesita PDO MySQL, intl, mbstring, ctype, iconv, DOM, XML y XMLWriter; para ejecutar todas las pruebas también necesita PDO SQLite. Composer comprobará el resto de requisitos de los paquetes.

Después de clonar el repositorio y copiar `.env.example` a `.env`:

1. Crea en tu MariaDB una base de datos `rentabilidad` y un usuario con permisos sobre ella, incluidos los necesarios para crear y modificar tablas.
2. Crea `.env.local` y configura la conexión real; por ejemplo, para MariaDB en el puerto habitual 3306:

   ```dotenv
   DATABASE_URL="mysql://TU_USUARIO:TU_CLAVE@127.0.0.1:3306/rentabilidad?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
   ```

   Sustituye usuario y contraseña; si contienen caracteres especiales, deben estar codificados para una URL. El ejemplo de `.env` usa 3307 porque ese es el puerto publicado por Docker. Si cambias `DATABASE_PORT` y conectas PHP desde el host, actualiza también `DATABASE_URL`.

3. Instala, prepara y arranca la aplicación:

   ```bash
   composer install --no-interaction
   composer check-platform-reqs
   php bin/console doctrine:migrations:migrate --no-interaction
   php bin/console doctrine:fixtures:load --append --no-interaction
   php bin/console doctrine:schema:validate
   vendor/bin/phpunit
   php -S 127.0.0.1:8000 -t public
   ```

Abre http://localhost:8000/admin y mantén esa terminal abierta; `Ctrl+C` detiene el servidor. El usuario que ejecuta PHP debe poder escribir en `var/`. `.env.local` sobrescribe `.env`; dentro del contenedor, la `DATABASE_URL` de `compose.yaml` tiene prioridad y conecta al servicio `database:3306`.

## 8. Resolver problemas frecuentes

| Problema | Qué comprobar |
| --- | --- |
| Docker no responde | Arranca Docker Desktop o el servicio Docker y comprueba `docker info`. En Linux, tu usuario debe tener acceso al servicio Docker. |
| Puerto ocupado | Cambia `APP_PORT` o `DATABASE_PORT` en `.env` y ejecuta `docker compose up -d`. |
| Falta `vendor/autoload.php` | Ejecuta `docker compose run --rm app composer install --no-interaction`. |
| No conecta con MariaDB | Revisa `docker compose ps` y `docker compose logs --tail=100 database`; espera a que figure como `healthy`. En Docker, el host de la base es `database`, no `localhost`. |
| Faltan tablas o columnas | Ejecuta `docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction`. |
| EasyAdmin se ve sin estilos | Ejecuta `docker compose run --rm app php bin/console assets:install public` y recarga la página. |
| Un producto no muestra margen | Comprueba que tiene precio mayor que cero y al menos una línea de coste. |
| Tras clonar no aparecen mis productos | Restaura una copia de la base de datos; Git no guarda su contenido. |

Para ver errores del servidor: `docker compose logs --tail=100 app`. Si cambias usuario o contraseña de MariaDB en Compose, recuerda que las variables de inicialización solo crean usuarios al estrenar un volumen; no cambian las credenciales de una base de datos existente.
