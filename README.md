# Costes y márgenes — segunda iteración

Symfony 7.4, PHP 8.3, Doctrine ORM, MariaDB 10.11 y EasyAdmin 4.

Al clonar el proyecto, crea la configuración local con `cp .env.example .env`. Los archivos `.env`, `.env.dev` y `.env.local` se mantienen fuera de Git. El ejemplo incluye solo valores de desarrollo.

## Arranque con Docker

Requiere Docker Compose y el servicio Docker activo. Desde esta carpeta:

```bash
docker compose build
docker compose up -d database
docker compose run --rm app composer install
docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction
docker compose run --rm app php bin/console doctrine:fixtures:load --append --no-interaction
docker compose up -d app
```

Abrir http://localhost:8000/admin. Detener con `docker compose down`; los datos persisten en un volumen.

## Sin Docker

PHP 8.3 con pdo_mysql, intl, mbstring, XML y Composer. MariaDB 10.11 con una base de datos y usuario propios. Ejecutar `composer install`, configurar `DATABASE_URL` en `.env.local` usando el ejemplo de `.env`, y ejecutar:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --append --no-interaction
php -S 127.0.0.1:8000 -t public
```

`.env` contiene valores de desarrollo; `.env.local` los sobreescribe y no se versiona. En Docker, `DATABASE_URL` en `compose.yaml` prevalece sobre ambos archivos y apunta al servicio `database`.

## Modelo y cálculos

- Product y CostType: nombre obligatorio y único.
- Cada producto tiene un color hexadecimal que se propone aleatoriamente al crearlo. El campo Color abre un selector al pulsar el cuadro y guarda la selección. En el gráfico, la venta usa ese color sólido y el coste un tono claro con rayas; los productos existentes reciben un color al aplicar la migración.
- Product incluye un precio de venta unitario opcional, decimal (12,4), no negativo, en euros sin IVA. Vacío significa precio pendiente; cero es un precio registrado.
- ProductCost: producto, tipo e importe unitario decimal (12,4), no negativo, en euros sin IVA. Se pueden añadir varias líneas del mismo tipo. Al borrar un producto se borran sus costes; un tipo utilizado no se puede borrar.
- Fixtures: Materia prima, Envase, Etiqueta, Sleeve, Dosificador, Fabricación, Transporte, Comisión y Otros. `--append` conserva los datos y permite repetir la carga sin duplicar tipos. Sin esa opción, Doctrine purga las tablas.
- `App\Pricing\MarginCalculator` recibe coste total y precio en la misma moneda, sin impuestos. `marginPercent(60, 100)` devuelve 40; `markupPercent(60, 100)` devuelve aproximadamente 66,67; `targetPriceForMargin(60, 40)` devuelve 100.
- Fórmulas: margen = `(precio − coste) / precio × 100`; markup = `(precio − coste) / coste × 100`; precio objetivo = `coste / (1 − margen / 100)`.
- Las pérdidas generan porcentajes negativos. El servicio rechaza valores negativos/no finitos, divisiones por cero y márgenes objetivo fuera de [0,100). Para el precio objetivo exige coste positivo. Los resultados no se redondean; el consumidor decide el redondeo monetario.

## Informe de rentabilidad

En `/admin`, el gráfico compara coste total unitario y precio de venta de cada producto sobre una escala común. Muestra beneficio por unidad, margen sobre el precio y un resumen de productos con beneficio, pérdidas, en equilibrio o pendientes. La tabla permite editar el precio y consultar los costes filtrados por producto.

Los costes se suman en una consulta y se recalculan al abrir el informe. Los productos sin precio o sin líneas de coste quedan pendientes: no se les atribuye beneficio. Una línea de coste a cero sí se considera registrada. Con precio cero se muestra el beneficio, pero el margen queda sin calcular. Los importes se muestran con cuatro decimales y los porcentajes con dos. Son resultados unitarios de los costes registrados, no totales de ventas.

Para actualizar una instalación existente, ejecutar `docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction`. Los productos existentes conservan sus datos y comienzan sin precio de venta.

## Verificación

```bash
docker compose run --rm app vendor/bin/phpunit
docker compose run --rm app php bin/console doctrine:schema:validate
```

Alternativa local: `vendor/bin/phpunit` y `php bin/console doctrine:schema:validate`.

Iteración local sin autenticación ni canales de venta. El servidor incluido escucha solo en localhost a través de Docker; antes de publicar se necesita autenticación y configuración de producción.
