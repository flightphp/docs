# Documentación de APM de FlightPHP

¡Bienvenido a FlightPHP APM—tu entrenador personal de rendimiento para aplicaciones! Esta guía es tu mapa de ruta para configurar, usar y dominar el Monitoreo de Rendimiento de Aplicaciones (APM) con FlightPHP. Ya sea que estés cazando solicitudes lentas o solo quieras geekearte con gráficos de latencia, te cubrimos. ¡Hagamos que tu app sea más rápida, tus usuarios más felices y tus sesiones de depuración un paseo!

Mira una [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) del dashboard para el sitio de Flight Docs.

![FlightPHP APM](/images/apm.png)

## Por qué APM importa

Imagina esto: tu app es un restaurante concurrido. Sin una forma de rastrear cuánto tiempo tardan los pedidos o dónde se atasca la cocina, estás adivinando por qué los clientes se van gruñendo. APM es tu sous-chef—vigila cada paso, desde las solicitudes entrantes hasta las consultas de base de datos, y marca cualquier cosa que te esté ralentizando. Las páginas lentas pierden usuarios (¡los estudios dicen que el 53% rebota si un sitio tarda más de 3 segundos en cargar!), y APM te ayuda a capturar esos problemas *antes* de que duelan. Es una paz mental proactiva—menos momentos de “¿por qué esto está roto?”, más victorias de “¡mira qué suave corre esto!”.

## Instalación

Comienza con Composer:

```bash
composer require flightphp/apm
```

Necesitarás:
- **PHP 7.4+**: Nos mantiene compatibles con distribuciones LTS de Linux mientras soporta PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: El framework ligero que estamos impulsando.

## Bases de datos compatibles

FlightPHP APM actualmente soporta las siguientes bases de datos para almacenar métricas:

- **SQLite3**: Simple, basada en archivos y genial para desarrollo local o apps pequeñas. Opción predeterminada en la mayoría de las configuraciones.
- **MySQL/MariaDB**: Ideal para proyectos más grandes o entornos de producción donde necesitas almacenamiento robusto y escalable.

Puedes elegir el tipo de base de datos durante el paso de configuración (ver abajo). Asegúrate de que tu entorno PHP tenga las extensiones necesarias instaladas (por ejemplo, `pdo_sqlite` o `pdo_mysql`).

## Primeros pasos

Aquí tienes tu paso a paso hacia la genialidad de APM:

### 1. Registrar el APM

Inserta esto en tu `index.php` o un archivo `services.php` para comenzar el rastreo:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Si estás agregando una conexión de base de datos
// Debe ser PdoWrapper o PdoQueryCapture de Tracy Extensions
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True requerido para habilitar el rastreo en el APM.
$Apm->addPdoConnection($pdo);
```

**¿Qué está pasando aquí?**
- `LoggerFactory::create()` toma tu configuración (más sobre eso pronto) y configura un logger—SQLite por defecto.
- `Apm` es la estrella—escucha los eventos de Flight (solicitudes, rutas, errores, etc.) y recopila métricas.
- `bindEventsToFlightInstance($app)` lo une todo a tu app de Flight.

**Consejo Pro: Muestreo**
Si tu app está ocupada, registrar *cada* solicitud podría sobrecargar las cosas. Usa una tasa de muestreo (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra el 10% de las solicitudes
```

Esto mantiene el rendimiento ágil mientras te da datos sólidos.

### 2. Configúralo

Ejecuta esto para crear tu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**¿Qué hace esto?**
- Lanza un asistente que pregunta de dónde vienen las métricas crudas (fuente) y dónde va la data procesada (destino).
- Por defecto es SQLite—por ejemplo, `sqlite:/tmp/apm_metrics.sqlite` para la fuente, otra para el destino.
- Terminarás con una configuración como:
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
Las métricas crudas se acumulan rápido (piensa en logs sin filtrar). El worker las procesa en un destino estructurado para el dashboard. ¡Mantiene todo ordenado!

### 3. Procesar métricas con el Worker

El worker convierte las métricas crudas en data lista para el dashboard. Ejecútalo una vez:

```bash
php vendor/bin/runway apm:worker
```

**¿Qué está haciendo?**
- Lee de tu fuente (por ejemplo, `apm_metrics.sqlite`).
- Procesa hasta 100 métricas (tamaño de lote predeterminado) en tu destino.
- Se detiene cuando termina o si no quedan métricas.

**Manténlo ejecutándose**
Para apps en vivo, querrás procesamiento continuo. Aquí tienes tus opciones:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Se ejecuta para siempre, procesando métricas a medida que llegan. Genial para dev o configuraciones pequeñas.

- **Crontab**:
  Agrega esto a tu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Se ejecuta cada minuto—perfecto para producción.

- **Tmux/Screen**:
  Inicia una sesión desmontable:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, luego D para desmontar; `tmux attach -t apm-worker` para reconectar
  ```
  Lo mantiene ejecutándose incluso si cierras sesión.

- **Ajustes personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Procesa 50 métricas a la vez.
  - `--max_messages 1000`: Se detiene después de 1000 métricas.
  - `--timeout 300`: Sale después de 5 minutos.

**¿Por qué molestarse?**
Sin el worker, tu dashboard está vacío. Es el puente entre logs crudos e insights accionables.

### 4. Lanzar el Dashboard

Ve los vitales de tu app:

```bash
php vendor/bin/runway apm:dashboard
```

**¿Qué es esto?**
- Inicia un servidor PHP en `http://localhost:8001/apm/dashboard`.
- Muestra logs de solicitudes, rutas lentas, tasas de error y más.

**Personalízalo**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Accesible desde cualquier IP (útil para visualización remota).
- `--port 8080`: Usa un puerto diferente si 8001 está ocupado.
- `--php-path`: Apunta a PHP si no está en tu PATH.

¡Abre la URL en tu navegador y explora!

#### Modo Producción

Para producción, podrías tener que probar algunas técnicas para que el dashboard se ejecute, ya que probablemente hay firewalls y otras medidas de seguridad en su lugar. Aquí hay algunas opciones:

- **Usa un Proxy Inverso**: Configura Nginx o Apache para reenviar solicitudes al dashboard.
- **Túnel SSH**: Si puedes SSH al servidor, usa `ssh -L 8080:localhost:8001 youruser@yourserver` para tunelizar el dashboard a tu máquina local.
- **VPN**: Si tu servidor está detrás de una VPN, conéctate a ella y accede al dashboard directamente.
- **Configura Firewall**: Abre el puerto 8001 para tu IP o la red del servidor. (o el puerto que hayas configurado).
- **Configura Apache/Nginx**: Si tienes un servidor web frente a tu aplicación, puedes configurarlo para un dominio o subdominio. Si haces esto, configurarás el document root a `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### ¿Quieres un dashboard diferente?

¡Puedes construir tu propio dashboard si quieres! Mira el directorio vendor/flightphp/apm/src/apm/presenter para ideas sobre cómo presentar los datos para tu propio dashboard!

## Características del Dashboard

El dashboard es tu HQ de APM—aquí está lo que verás:

- **Registro de Solicitudes**: Cada solicitud con timestamp, URL, código de respuesta y tiempo total. Haz clic en “Detalles” para middleware, consultas y errores.
- **Solicitudes Más Lentas**: Top 5 solicitudes que consumen tiempo (por ejemplo, “/api/heavy” en 2.5s).
- **Rutas Más Lentas**: Top 5 rutas por tiempo promedio—genial para detectar patrones.
- **Tasa de Error**: Porcentaje de solicitudes fallidas (por ejemplo, 2.3% de 500s).
- **Percentiles de Latencia**: 95th (p95) y 99th (p99) tiempos de respuesta—conoce tus escenarios de peor caso.
- **Gráfico de Código de Respuesta**: Visualiza 200s, 404s, 500s a lo largo del tiempo.
- **Consultas Largas/Middleware**: Top 5 llamadas lentas a base de datos y capas de middleware.
- **Acierto/Fallo de Caché**: Cuánto salva tu caché el día.

**Extras**:
- Filtra por “Última Hora”, “Último Día” o “Última Semana”.
- Cambia a modo oscuro para esas sesiones nocturnas.

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Caché: Acierto en `user_list` (5ms)

## Agregar Eventos Personalizados

Rastrea cualquier cosa—como una llamada API o proceso de pago:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**¿Dónde aparece?**
En los detalles de solicitud del dashboard bajo “Eventos Personalizados”—expandible con formato JSON bonito.

**Caso de Uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
¡Ahora verás si esa API está arrastrando tu app hacia abajo!

## Monitoreo de Base de Datos

Rastrea consultas PDO así:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True requerido para habilitar el rastreo en el APM.
$Apm->addPdoConnection($pdo);
```

**Lo que Obtienes**:
- Texto de consulta (por ejemplo, `SELECT * FROM users WHERE id = ?`)
- Tiempo de ejecución (por ejemplo, 0.015s)
- Conteo de filas (por ejemplo, 42)

**Advertencia**:
- **Opcional**: Sáltate esto si no necesitas rastreo de BD.
- **Solo PdoWrapper**: PDO core no está enganchado aún—¡mantente atento!
- **Advertencia de Rendimiento**: Registrar cada consulta en un sitio pesado en BD puede ralentizar las cosas. Usa muestreo (`$Apm = new Apm($ApmLogger, 0.1)`) para aligerar la carga.

**Salida de Ejemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tiempo: 0.023s
- Filas: 15

## Opciones del Worker

Ajusta el worker a tu gusto:

- `--timeout 300`: Se detiene después de 5 minutos—bueno para pruebas.
- `--max_messages 500`: Limita a 500 métricas—lo mantiene finito.
- `--batch_size 200`: Procesa 200 a la vez—equilibra velocidad y memoria.
- `--daemon`: Se ejecuta sin parar—ideal para monitoreo en vivo.

**Ejemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Se ejecuta por una hora, procesando 100 métricas a la vez.

## ID de Solicitud en la App

Cada solicitud tiene un ID de solicitud único para rastreo. Puedes usar este ID en tu app para correlacionar logs y métricas. Por ejemplo, puedes agregar el ID de solicitud a una página de error:

```php
Flight::map('error', function($message) {
	// Obtén el ID de solicitud del encabezado de respuesta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Adicionalmente podrías obtenerlo de la variable de Flight
	// Este método no funcionará bien en swoole u otras plataformas async.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Actualización

Si estás actualizando a una versión más nueva del APM, hay una posibilidad de que haya migraciones de base de datos que necesiten ejecutarse. Puedes hacerlo ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:migrate
```
Esto ejecutará cualquier migración necesaria para actualizar el esquema de la base de datos a la versión más reciente.

**Nota:** Si tu base de datos de APM es grande en tamaño, estas migraciones pueden tardar algo de tiempo en ejecutarse. Podrías querer ejecutar este comando durante horas de bajo pico.

### Actualizando de 0.4.3 -> 0.5.0

Si estás actualizando de 0.4.3 a 0.5.0, necesitarás ejecutar el siguiente comando:

```bash
php vendor/bin/runway apm:config-migrate
```

Esto migrará tu configuración del formato antiguo usando el archivo `.runway-config.json` al nuevo formato que almacena las claves/valores en el archivo `config.php`.

## Purgar Datos Antiguos

Para mantener tu base de datos ordenada, puedes purgar datos antiguos. Esto es especialmente útil si estás ejecutando una app ocupada y quieres mantener el tamaño de la base de datos manejable.
Puedes hacerlo ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:purge
```
Esto eliminará todos los datos más antiguos que 30 días de la base de datos. Puedes ajustar el número de días pasando un valor diferente a la opción `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Esto eliminará todos los datos más antiguos que 7 días de la base de datos.

## Solución de Problemas

¿Atascado? Prueba estos:

- **¿No hay datos en el Dashboard?**
  - ¿Está ejecutándose el worker? Verifica `ps aux | grep apm:worker`.
  - ¿Las rutas de configuración coinciden? Verifica que los DSNs en `.runway-config.json` apunten a archivos reales.
  - Ejecuta `php vendor/bin/runway apm:worker` manualmente para procesar métricas pendientes.

- **¿Errores en el Worker?**
  - Mira tus archivos SQLite (por ejemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifica los logs de PHP para trazas de pila.

- **¿El Dashboard no inicia?**
  - ¿Puerto 8001 en uso? Usa `--port 8080`.
  - ¿PHP no encontrado? Usa `--php-path /usr/bin/php`.
  - ¿Firewall bloqueando? Abre el puerto o usa `--host localhost`.

- **¿Demasiado lento?**
  - Baja la tasa de muestreo: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduce el tamaño de lote: `--batch_size 20`.

- **¿No rastrea Excepciones/Errores?**
  - Si tienes [Tracy](https://tracy.nette.org/) habilitado para tu proyecto, sobrescribirá el manejo de errores de Flight. Necesitarás deshabilitar Tracy y luego asegurarte de que `Flight::set('flight.handle_errors', true);` esté configurado.

- **¿No rastrea Consultas de Base de Datos?**
  - Asegúrate de estar usando `PdoWrapper` para tus conexiones de base de datos.
  - Asegúrate de que el último argumento en el constructor sea `true`.