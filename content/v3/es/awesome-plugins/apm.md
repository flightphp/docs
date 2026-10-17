# Documentación de FlightPHP APM

Bienvenido a FlightPHP APM—¡el entrenador personal de rendimiento de tu app! Esta guía es tu hoja de ruta para configurar, usar y dominar el Monitoreo del Rendimiento de Aplicaciones (APM) con FlightPHP. Ya sea que estés rastreando solicitudes lentas o simplemente quieras emocionarte con los gráficos de latencia, te tenemos cubierto. ¡Hagamos que tu app sea más rápida, tus usuarios más felices y tus sesiones de depuración pan comido!

Consulta una [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) del panel para el sitio Flight Docs.

![FlightPHP APM](/images/apm.png)

## Por qué importa APM

Imagina esto: tu app es un restaurante concurrido. Sin una forma de rastrear cuánto tardan los pedidos o dónde se está atascando la cocina, adivinas por qué los clientes se van molestos. APM es tu sous-chef: vigila cada paso, desde las solicitudes entrantes hasta las consultas a la base de datos, y señala cualquier cosa que te esté ralentizando. Las páginas lentas pierden usuarios (los estudios dicen que el 53 % rebota si un sitio tarda más de 3 segundos en cargar), y APM te ayuda a detectar esos problemas *antes* de que duelan. Es tranquilidad proactiva: menos momentos de “¿por qué está roto esto?” y más victorias de “¡mira qué bien funciona!”.

## Instalación

Comienza con Composer:

```bash
composer require flightphp/apm
```

Necesitarás:
- **PHP 7.4+**: nos mantiene compatibles con distribuciones Linux LTS mientras admite PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: el framework ligero que estamos potenciando.

## Bases de datos compatibles

FlightPHP APM actualmente admite las siguientes bases de datos para almacenar métricas:

- **SQLite3**: simple, basada en archivos y excelente para desarrollo local o apps pequeñas. Opción predeterminada en la mayoría de configuraciones.
- **MySQL/MariaDB**: ideal para proyectos más grandes o entornos de producción donde necesitas almacenamiento robusto y escalable.

Puedes elegir tu tipo de base de datos durante el paso de configuración (ver abajo). Asegúrate de que tu entorno PHP tenga las extensiones necesarias instaladas (p. ej., `pdo_sqlite` o `pdo_mysql`).

## Primeros pasos

Aquí tienes tu guía paso a paso hacia la grandeza de APM:

### 1. Registra el APM

Coloca esto en tu `index.php` o en un archivo `services.php` para empezar a rastrear:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Si vas a agregar una conexión a base de datos
// Prefiere SimplePdo (o PdoQueryCapture de Tracy Extensions en desarrollo).
// Habilita el seguimiento de consultas del APM mediante el array de opciones (5.º argumento).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // requerido para capturar consultas para el APM
]);
$Apm->addPdoConnection($pdo);
```

**¿Qué está pasando aquí?**
- `LoggerFactory::create()` toma tu configuración (más sobre eso pronto) y configura un logger—SQLite de forma predeterminada.
- `Apm` es la estrella: escucha los eventos de Flight (solicitudes, rutas, errores, etc.) y recopila métricas.
- `bindEventsToFlightInstance($app)` lo vincula todo a tu app de Flight.

**Consejo profesional: muestreo**
Si tu app tiene mucho tráfico, registrar cada solicitud podría sobrecargar las cosas. Usa una tasa de muestreo (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra el 10 % de las solicitudes
```

Esto mantiene el rendimiento ágil y aun así te da datos sólidos.

### 2. Configúralo

Ejecuta esto para generar tu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**¿Qué hace esto?**
- Lanza un asistente que pregunta de dónde vienen las métricas sin procesar (origen) y a dónde van los datos procesados (destino).
- El valor predeterminado es SQLite—p. ej., `sqlite:/tmp/apm_metrics.sqlite` para el origen y otro para el destino.
- Terminarás con una configuración como esta:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Este proceso también preguntará si quieres ejecutar las migraciones para esta configuración. Si es la primera vez que lo configuras, la respuesta es sí.

**¿Por qué dos ubicaciones?**
Las métricas sin procesar se acumulan rápido (piensa en logs sin filtrar). El worker las procesa en un destino estructurado para el panel. ¡Mantiene todo ordenado!

### 3. Procesa métricas con el worker

El worker convierte las métricas sin procesar en datos listos para el panel. Ejecútalo una vez:

```bash
php vendor/bin/runway apm:worker
```

**¿Qué está haciendo?**
- Lee desde tu origen (p. ej., `apm_metrics.sqlite`).
- Procesa hasta 100 métricas (tamaño de lote predeterminado) en tu destino.
- Se detiene cuando termina o si no quedan métricas.

**Mantenerlo en ejecución**
Para apps en vivo, querrás un procesamiento continuo. Estas son tus opciones:

- **Modo daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Se ejecuta indefinidamente, procesando métricas a medida que llegan. Excelente para desarrollo o configuraciones pequeñas.

- **Crontab**:
  Agrega esto a tu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Se ejecuta cada minuto—perfecto para producción.

- **Tmux/Screen**:
  Inicia una sesión separable:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, luego D para separar; `tmux attach -t apm-worker` para reconectar
  ```
  Lo mantiene en ejecución incluso si cierras sesión.

- **Ajustes personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: procesa 50 métricas a la vez.
  - `--max_messages 1000`: detente después de 1000 métricas.
  - `--timeout 300`: sal después de 5 minutos.

**¿Por qué molestarse?**
Sin el worker, tu panel está vacío. Es el puente entre los logs sin procesar y los conocimientos accionables.

### 4. Inicia el panel

Consulta los signos vitales de tu app:

```bash
php vendor/bin/runway apm:dashboard
```

**¿Qué es esto?**
- Levanta un servidor PHP en `http://localhost:8001/apm/dashboard`.
- Muestra logs de solicitudes, rutas lentas, tasas de error y más.

**Personalízalo**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: accesible desde cualquier IP (útil para visualización remota).
- `--port 8080`: usa un puerto diferente si el 8001 está ocupado.
- `--php-path`: apunta a PHP si no está en tu PATH.

Abre la URL en tu navegador y explora.

#### Modo de producción

Para producción, es posible que tengas que probar algunas técnicas para conseguir que el panel se ejecute, ya que probablemente haya cortafuegos y otras medidas de seguridad implementadas. Aquí tienes algunas opciones:

- **Usa un proxy inverso**: configura Nginx o Apache para reenviar solicitudes al panel.
- **Túnel SSH**: si puedes acceder por SSH al servidor, usa `ssh -L 8080:localhost:8001
youruser@yourserver` para tunelizar el panel a tu máquina local.
- **VPN**: si tu servidor está detrás de una VPN, conéctate a ella y accede directamente al panel.
- **Configura el cortafuegos**: abre el puerto 8001 para tu IP o la red del servidor (o el puerto que hayas configurado).
- **Configura Apache/Nginx**: si tienes un servidor web delante de tu aplicación, puedes configurarlo para un dominio o subdominio. Apunta ese host a `dashboard/index.php` (el servidor integrado lo usa como enrutador). Los archivos estáticos están junto a él en `dashboard/css` y `dashboard/js`.
- **Autenticación básica**: establece `apm.dashboard_auth.user` y `apm.dashboard_auth.password` en la configuración de runway. Si cualquiera de los valores está vacío, el panel permanece abierto. Es una única contraseña compartida, no cuentas de usuario de la aplicación.

#### ¿Quieres un panel diferente?

¡Puedes crear tu propio panel si quieres! Consulta el directorio `vendor/flightphp/apm/src/apm/presenter` para inspirarte sobre cómo presentar los datos para tu propio panel.

## Funciones del panel

El panel se abre con los números, luego los gráficos y después el log de solicitudes.

- **Fila de estadísticas**: recuento de solicitudes, tasa de error, p95, p99, latencia promedio, rendimiento por minuto y tasa de aciertos de caché.
- **Pendientes del worker**: cuántas métricas sin procesar siguen en `apm_metrics_log`. Un número alto significa que el worker está retrasado. Un panel vacío con 0 pendientes significa que no se ha recopilado nada.
- **Gráfico de latencia**: latencia promedio y recuento de solicitudes para los mismos intervalos. La última hora usa intervalos de 5 minutos, el último día usa intervalos de 15 minutos y la última semana usa intervalos de 6 horas. Un rango personalizado elige un intervalo que mantiene unos 40 puntos. Las horas se almacenan en UTC y se dibujan en la zona horaria que elijas.
- **Gráfico de estados**: recuentos apilados de `2xx`, `3xx`, `4xx` y `5xx`. Haz clic en una serie para filtrar el log a esa clase.
- **Rutas lentas, middleware, consultas y solicitudes**: cinco de cada una. Las consultas se agrupan de modo que `WHERE id = 1` y `WHERE id = 2` cuentan como una sola sentencia. Haz clic en una ruta, una solicitud lenta o un grupo de errores para filtrar el log.
- **Eventos personalizados**: cada tipo de evento personalizado que dispara tu app, con su recuento, cuándo se vio por última vez y una pequeña línea de tendencia para la ventana seleccionada. Ordenados por recuento. Respeta el rango de tiempo, la opción Hide bots y los demás filtros. Haz clic en un tipo para filtrar el log a ese tipo.
- **Log de solicitudes**: método, URL, estado, tiempo, memoria, host y marca de bot. Los detalles se cargan cuando abres la fila e incluyen una barra de tiempos, vistas, parámetros de consulta, el rastro del error y eventos personalizados.
- **Hide bots**: deja el tráfico de bots fuera de los agregados y del log. Los rastreadores de búsqueda y los rastreadores de LLM (GPTBot, ClaudeBot, PerplexityBot, SearxNG y otros) se marcan como bots.
- **Rango personalizado**: elige una ventana desde/hasta además de la última hora, día o semana.
- **Filtros**: URL, estado, ID de solicitud, ruta, IP, host, sesión, user agent, tipo de evento personalizado, datos del evento y mensaje de error. La cadena de consulta los mantiene entre actualizaciones.

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Tiempo de renderizado de vista
- Caché: acierto en `user_list` (5ms)

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Caché: acierto en `user_list` (5ms)

## Agregar eventos personalizados

Rastrea cualquier cosa, como una llamada a la API o un proceso de pago:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**¿Dónde aparece?**
En los detalles de la solicitud del panel, bajo “Eventos personalizados”, se puede expandir con un formato JSON bonito. El panel **Eventos personalizados** también cuenta cada tipo por ti, así que un evento rápido `docs.markdown_served` es todo lo que necesitas para responder “¿con qué frecuencia ocurre esto?”.

**Caso de uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
¡Ahora verás si esa API está ralentizando tu app!

## Monitoreo de base de datos

Rastrea consultas PDO así:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // requerido para capturar consultas para el APM
]);
$Apm->addPdoConnection($pdo);
```

**Qué obtienes**:
- Texto de la consulta (p. ej., `SELECT * FROM users WHERE id = ?`)
- Tiempo de ejecución (p. ej., 0.015s)
- Recuento de filas (p. ej., 42)

**Atención**:
- **Opcional**: omite esto si no necesitas el seguimiento de la base de datos.
- **SimplePdo (preferido)**: usa `SimplePdo` con `trackApmQueries => true`. El obsoleto `PdoWrapper` todavía funciona (5.º argumento del constructor `true`). El PDO central sin procesar aún no está enganchado—¡mantente atento!
- **Advertencia de rendimiento**: registrar cada consulta en un sitio con mucha carga de base de datos puede ralentizar las cosas. Usa muestreo (`$Apm = new Apm($ApmLogger, 0.1)`) para reducir la carga.

**Salida de ejemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tiempo: 0.023s
- Filas: 15

## Opciones del worker

Ajusta el worker a tu gusto:

- `--timeout 300`: se detiene después de 5 minutos—bueno para pruebas.
- `--max_messages 500`: limita a 500 métricas—lo mantiene finito.
- `--batch_size 200`: procesa 200 a la vez—equilibra velocidad y memoria.
- `--daemon`: se ejecuta sin parar—ideal para monitoreo en vivo.

**Ejemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Se ejecuta durante una hora, procesando 100 métricas a la vez.

## ID de solicitud en la app

Cada solicitud tiene un ID de solicitud único para el seguimiento. Puedes usar este ID en tu app para correlacionar logs y métricas. Por ejemplo, puedes agregar el ID de solicitud a una página de error:

```php
Flight::map('error', function($message) {
	// Obtén el ID de solicitud del encabezado de respuesta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Además, podrías obtenerlo de la variable de Flight
	// Este método no funcionará bien en swoole u otras plataformas asíncronas.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Actualización

Si estás actualizando a una versión más reciente del APM, es posible que haya migraciones de base de datos que deban ejecutarse. Puedes hacerlo ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:migrate
```
Esto ejecutará cualquier migración necesaria para actualizar el esquema de la base de datos a la última versión.

**Nota:** si tu base de datos del APM es grande, estas migraciones pueden tardar algún tiempo en ejecutarse. Ejecútalas durante un periodo de poca actividad. El worker puede seguir ejecutándose. Las migraciones de índices no reescriben las filas recopiladas.

### Actualización posterior a 0.5.2 (panel e índices)

0.5.2 solo amplió la detección de bots. Los archivos de índices que aparecen a continuación se incluyen en la versión posterior a 0.5.2.

Primero haz una copia de seguridad de la base de datos de destino. Copia el archivo SQLite o usa `mysqldump` para el esquema de MySQL/MariaDB.

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

Eso aplica:

- SQLite: `0006-dest-indexes.sql` (los índices `request_dt` se eliminaron cuando `0005` reconstruyó `apm_requests`)
- MySQL/MariaDB: `0002-dest-indexes.sql` (`request_dt` y los demás filtros del panel)

`apm:migrate` registra el nombre del archivo y lo omite la próxima vez. Ejecutarlo dos veces es seguro. `apm:purge` compara `request_dt` con un corte en UTC, lo que coincide con cómo se almacenan las filas. La primera purga después de esta actualización puede conservar una ventana diferente si la zona horaria del servidor no era UTC antes.

`X-Flight-Request-Id` coincide con el `request_token` almacenado para esa solicitud.

### Actualización de 0.4.3 -> 0.5.0

Si estás actualizando de 0.4.3 a 0.5.0, necesitarás ejecutar el siguiente comando:

```bash
php vendor/bin/runway apm:config-migrate
```

Esto migrará tu configuración del formato antiguo usando el archivo `.runway-config.json` al nuevo formato, que almacena las claves/valores en el archivo `config.php`.

## Purgar datos antiguos

Para mantener tu base de datos ordenada, puedes purgar datos antiguos. Esto es especialmente útil si tienes una app con mucho tráfico y quieres mantener manejable el tamaño de la base de datos.
Puedes hacerlo ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:purge
```
Esto eliminará de la base de datos todos los datos con más de 30 días. Puedes ajustar el número de días pasando un valor diferente a la opción `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Esto eliminará de la base de datos todos los datos con más de 7 días.

## Solución de problemas

¿Atascado? Prueba esto:

- **¿No hay datos en el panel?**
  - ¿Se está ejecutando el worker? Comprueba `ps aux | grep apm:worker`.
  - ¿Coinciden las rutas de configuración? Verifica que los DSN de `.runway-config.json` apunten a archivos reales.
  - Ejecuta `php vendor/bin/runway apm:worker` manualmente para procesar las métricas pendientes.

- **¿Errores del worker?**
  - Echa un vistazo a tus archivos SQLite (p. ej., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Revisa los logs de PHP en busca de rastros de pila.

- **¿El panel no inicia?**
  - ¿El puerto 8001 está en uso? Usa `--port 8080`.
  - ¿No se encuentra PHP? Usa `--php-path /usr/bin/php`.
  - ¿El cortafuegos lo bloquea? Abre el puerto o usa `--host localhost`.

- **¿Demasiado lento?**
  - Baja la tasa de muestreo: `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Reduce el tamaño de lote: `--batch_size 20`.

- **¿No rastrea excepciones/errores?**
  - Si tienes [Tracy](https://tracy.nette.org/) habilitado para tu proyecto, anulará el manejo de errores de Flight. Tendrás que deshabilitar Tracy y luego asegurarte de que `Flight::set('flight.handle_errors', true);` esté establecido.

- **¿No rastrea consultas a la base de datos?**
  - Prefiere `SimplePdo` con `['trackApmQueries' => true]` como 5.º argumento del constructor (array de opciones).
  - Si todavía usas el obsoleto `PdoWrapper`, pasa `true` como 5.º argumento.
  - Llama a `$Apm->addPdoConnection($pdo)` después de crear la conexión.