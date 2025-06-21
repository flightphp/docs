# Documentación de FlightPHP APM

Bienvenido a FlightPHP APM, tu entrenador personal de rendimiento para tu aplicación. Esta guía es tu mapa para configurar, usar y dominar la Monitorización de Rendimiento de Aplicaciones (APM) con FlightPHP. Ya sea que estés cazando solicitudes lentas o simplemente quieras emocionarte con gráficos de latencia, te tenemos cubierto. ¡Hagamos que tu aplicación sea más rápida, tus usuarios más felices y tus sesiones de depuración una brisa!

## Por qué importa el APM

Imagina esto: tu aplicación es un restaurante ocupado. Sin una forma de rastrear cuánto tardan los pedidos o dónde se atasca la cocina, estás adivinando por qué los clientes se van enojados. El APM es tu sous-chef, observa cada paso, desde las solicitudes entrantes hasta las consultas de la base de datos, y marca cualquier cosa que te esté ralentizando. Las páginas lentas pierden usuarios (estudios dicen que el 53% rebota si un sitio tarda más de 3 segundos en cargar), y el APM te ayuda a detectar esos problemas *antes* de que piquen. Es una paz proactiva, con menos momentos de "¿por qué esto está roto?" y más victorias de "¡mira lo bien que funciona esto!".

## Instalación

Comienza con Composer:

```bash
composer require flightphp/apm
```

Necesitarás:
- **PHP 7.4+**: Mantiene la compatibilidad con distribuciones LTS de Linux mientras soporta PHP moderno.
- **[Núcleo de FlightPHP](https://github.com/flightphp/core) v3.15+**: El framework ligero que estamos mejorando.

## Comenzando

Aquí está tu guía paso a paso para el asombro del APM:

### 1. Registrar el APM

Agrega esto en tu archivo `index.php` o `services.php` para comenzar a rastrear:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
// ¿Qué está pasando aquí?
// - LoggerFactory::create() toma tu configuración (más sobre eso pronto) y configura un registrador, SQLite por defecto.
// - Apm es la estrella, escucha eventos de Flight (solicitudes, rutas, errores, etc.) y recopila métricas.
// - bindEventsToFlightInstance($app) lo une todo a tu aplicación de Flight.

$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Consejo Pro: Muestreo**
Si tu aplicación está ocupada, registrar *cada* solicitud podría sobrecargar las cosas. Usa una tasa de muestreo (de 0.0 a 1.0):

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
- Lanza un asistente que pregunta de dónde vienen las métricas en bruto (fuente) y a dónde van los datos procesados (destino).
- Por defecto es SQLite, por ejemplo, `sqlite:/tmp/apm_metrics.sqlite` para la fuente, y otra para el destino.
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

> Este proceso también preguntará si quieres ejecutar las migraciones para esta configuración. Si lo estás configurando por primera vez, la respuesta es sí.

**¿Por qué dos ubicaciones?**
Las métricas en bruto se acumulan rápidamente (piensa en registros sin filtrar). El trabajador las procesa en un destino estructurado para el panel de control. ¡Mantiene todo ordenado!

### 3. Procesar métricas con el trabajador

El trabajador convierte las métricas en bruto en datos listos para el panel de control. Ejecuta esto una vez:

```bash
php vendor/bin/runway apm:worker
```

**¿Qué está haciendo?**
- Lee de tu fuente (por ejemplo, `apm_metrics.sqlite`).
- Procesa hasta 100 métricas (tamaño de lote por defecto) en tu destino.
- Se detiene cuando termina o si no hay métricas leftidas.

**Mantenerlo en ejecución**
Para aplicaciones en vivo, querrás un procesamiento continuo. Aquí están tus opciones:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Se ejecuta para siempre, procesando métricas a medida que llegan. Genial para desarrollo o configuraciones pequeñas.

- **Crontab**:
  Agrega esto a tu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Se dispara cada minuto, perfecto para producción.

- **Tmux/Screen**:
  Inicia una sesión detachable:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, luego D para desconectar; `tmux attach -t apm-worker` para reconectar
  ```
  Mantiene en ejecución incluso si te desconectas.

- **Ajustes personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Procesar 50 métricas a la vez.
  - `--max_messages 1000`: Detenerse después de 1000 métricas.
  - `--timeout 300`: Salir después de 5 minutos.

**¿Por qué molestarse?**
Sin el trabajador, tu panel de control está vacío. Es el puente entre los registros en bruto y las ideas accionables.

### 4. Lanzar el panel de control

Ve los signos vitales de tu aplicación:

```bash
php vendor/bin/runway apm:dashboard
```

**¿Qué es esto?**
- Inicia un servidor PHP en `http://localhost:8001/apm/dashboard`.
- Muestra registros de solicitudes, rutas lentas, tasas de error y más.

**Personalízalo**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Accesible desde cualquier IP (útil para visualización remota).
- `--port 8080`: Usa un puerto diferente si 8001 está ocupado.
- `--php-path`: Apunta a PHP si no está en tu PATH.

¡Abre la URL en tu navegador y explora!

#### Modo de producción

Para producción, puedes tener que probar algunas técnicas para que el panel de control funcione, ya que probablemente haya firewalls y otras medidas de seguridad. Aquí hay algunas opciones:

- **Usa un Proxy Inverso**: Configura Nginx o Apache para reenviar solicitudes al panel de control.
- **Túnel SSH**: Si puedes SSH al servidor, usa `ssh -L 8080:localhost:8001 youruser@yourserver` para tunelizar el panel de control a tu máquina local.
- **VPN**: Si tu servidor está detrás de una VPN, conéctate a ella y accede al panel de control directamente.
- **Configura Firewall**: Abre el puerto 8001 para tu IP o la red del servidor (o el puerto que hayas establecido).
- **Configura Apache/Nginx**: Si tienes un servidor web frente a tu aplicación, puedes configurarlo para un dominio o subdominio. Si lo haces, establecerás la raíz de documentos en `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### ¿Quieres un panel de control diferente?

¡Puedes construir tu propio panel de control si quieres! Mira el directorio `vendor/flightphp/apm/src/apm/presenter` para ideas sobre cómo presentar los datos para tu propio panel de control.

## Características del panel de control

El panel de control es tu sede del APM, aquí está lo que verás:

- **Registro de Solicitudes**: Cada solicitud con marca de tiempo, URL, código de respuesta y tiempo total. Haz clic en "Detalles" para middleware, consultas y errores.
- **Solicitudes Más Lentas**: Las 5 solicitudes principales que consumen tiempo (por ejemplo, “/api/heavy” en 2.5s).
- **Rutas Más Lentas**: Las 5 rutas por tiempo promedio, genial para detectar patrones.
- **Tasa de Error**: Porcentaje de solicitudes que fallan (por ejemplo, 2.3% de 500s).
- **Percentiles de Latencia**: 95.º (p95) y 99.º (p99) tiempos de respuesta, conoce tus escenarios en el peor caso.
- **Gráfico de Códigos de Respuesta**: Visualiza 200s, 404s, 500s a lo largo del tiempo.
- **Consultas/Middleware Largas**: Las 5 principales llamadas de base de datos lentas y capas de middleware.
- **Aciertos/Fallos de Caché**: Con qué frecuencia tu caché salva el día.

**Extras**:
- Filtra por “Última Hora”, “Último Día” o “Última Semana”.
- Activa el modo oscuro para esas sesiones nocturnas.

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Caché: Acierto en `user_list` (5ms)

## Agregar eventos personalizados

Rastrea cualquier cosa, como una llamada a API o un proceso de pago:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
// ¿Dónde aparece?
// En los detalles de la solicitud del panel de control bajo "Eventos Personalizados", expandible con formato JSON bonito.

// Caso de uso:
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
¡Ahora verás si esa API está ralentizando tu aplicación!

## Monitorización de la base de datos

Rastrea consultas PDO de esta manera:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
// Lo que obtienes:
// - Texto de la consulta (por ejemplo, `SELECT * FROM users WHERE id = ?`)
// - Tiempo de ejecución (por ejemplo, 0.015s)
// - Conteo de filas (por ejemplo, 42)

// Advertencia:
// - Opcional: Salta esto si no necesitas rastreo de DB.
// - Solo PdoWrapper: PDO central no está conectado todavía, ¡quédate atento!
// - Advertencia de rendimiento: Registrar cada consulta en un sitio con base de datos pesada puede ralentizar las cosas. Usa muestreo (`$Apm = new Apm($ApmLogger, 0.1)`) para aligerar la carga.

**Salida de ejemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tiempo: 0.023s
- Filas: 15

## Opciones del trabajador

Ajusta el trabajador a tu gusto:

- `--timeout 300`: Se detiene después de 5 minutos, bueno para pruebas.
- `--max_messages 500`: Limita a 500 métricas, lo mantiene finito.
- `--batch_size 200`: Procesar 200 a la vez, equilibra velocidad y memoria.
- `--daemon`: Se ejecuta sin parar, ideal para monitoreo en vivo.

**Ejemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Se ejecuta durante una hora, procesando 100 métricas a la vez.

## ID de solicitud en la aplicación

Cada solicitud tiene un ID de solicitud único para rastreo. Puedes usar este ID en tu aplicación para correlacionar registros y métricas. Por ejemplo, puedes agregar el ID de solicitud a una página de error:

```php
Flight::map('error', function($message) {
	// Obtén el ID de solicitud del encabezado de respuesta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Además, podrías obtenerlo de la variable de Flight
	// Este método no funcionará bien en plataformas swoole u otras asíncronas.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (ID de Solicitud: $requestId)";
});
```

## Actualizando

Si estás actualizando a una versión más nueva del APM, es posible que haya migraciones de base de datos que necesiten ejecutarse. Puedes hacer esto ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:migrate
```
Esto ejecutará cualquier migración necesaria para actualizar el esquema de la base de datos a la última versión.

**Nota:** Si tu base de datos de APM es grande, estas migraciones pueden tardar un tiempo. Puede que quieras ejecutar este comando durante horas de bajo pico.

## Purgando datos antiguos

Para mantener tu base de datos ordenada, puedes purgar datos antiguos. Esto es especialmente útil si estás ejecutando una aplicación ocupada y quieres mantener el tamaño de la base de datos manejable.
Puedes hacer esto ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:purge
```
Esto eliminará todos los datos más antiguos de 30 días de la base de datos. Puedes ajustar el número de días pasando un valor diferente a la opción `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Esto eliminará todos los datos más antiguos de 7 días de la base de datos.

## Resolución de problemas

¿Atascado? Prueba estas:

- **¿No hay datos en el panel de control?**
  - ¿Está ejecutándose el trabajador? Verifica `ps aux | grep apm:worker`.
  - ¿Coinciden las rutas de configuración? Verifica que las DSNs en `.runway-config.json` apunten a archivos reales.
  - Ejecuta `php vendor/bin/runway apm:worker` manualmente para procesar métricas pendientes.

- **¿Errores en el trabajador?**
  - Echa un vistazo a tus archivos SQLite (por ejemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifica los registros de PHP para rastros de pila.

- **¿El panel de control no se inicia?**
  - ¿El puerto 8001 está en uso? Usa `--port 8080`.
  - ¿PHP no encontrado? Usa `--php-path /usr/bin/php`.
  - ¿Firewall bloqueando? Abre el puerto o usa `--host localhost`.

- **¿Demasiado lento?**
  - Baja la tasa de muestreo: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduce el tamaño de lote: `--batch_size 20`.