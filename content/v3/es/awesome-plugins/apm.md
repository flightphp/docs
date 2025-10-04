# Documentación de APM de FlightPHP

¡Bienvenido a FlightPHP APM—el entrenador personal de rendimiento de tu aplicación! Esta guía es tu mapa de ruta para configurar, usar y dominar el Monitoreo de Rendimiento de Aplicaciones (APM) con FlightPHP. Ya sea que estés cazando solicitudes lentas o solo quieras geekearte con gráficos de latencia, te tenemos cubierto. ¡Hagamos que tu app sea más rápida, tus usuarios más felices y tus sesiones de depuración una brisa!

Mira una [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) del dashboard para el sitio de Flight Docs.

![FlightPHP APM](/images/apm.png)

## Por qué APM importa

Imagina esto: tu app es un restaurante concurrido. Sin una forma de rastrear cuánto tiempo tardan los pedidos o dónde se atasca la cocina, estás adivinando por qué los clientes se van gruñendo. APM es tu sous-chef—vigila cada paso, desde las solicitudes entrantes hasta las consultas de base de datos, y marca cualquier cosa que te esté ralentizando. Las páginas lentas pierden usuarios (¡los estudios dicen que el 53% rebota si un sitio tarda más de 3 segundos en cargar!), y APM te ayuda a capturar esos problemas *antes* de que duelan. Es una paz de mente proactiva—menos momentos de “¿por qué esto está roto?”, más victorias de “¡mira qué fluido corre esto!”.

## Instalación

Comienza con Composer:

```bash
composer require flightphp/apm
```

Necesitarás:
- **PHP 7.4+**: Nos mantiene compatibles con distribuciones Linux LTS mientras soporta PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: El framework ligero que estamos impulsando.

## Bases de datos compatibles

FlightPHP APM actualmente soporta las siguientes bases de datos para almacenar métricas:

- **SQLite3**: Simple, basada en archivos, y genial para desarrollo local o apps pequeñas. Opción predeterminada en la mayoría de las configuraciones.
- **MySQL/MariaDB**: Ideal para proyectos más grandes o entornos de producción donde necesitas almacenamiento robusto y escalable.

Puedes elegir el tipo de base de datos durante el paso de configuración (ver abajo). Asegúrate de que tu entorno PHP tenga las extensiones necesarias instaladas (p.ej., `pdo_sqlite` o `pdo_mysql`).

## Comenzando

Aquí tienes tu guía paso a paso hacia la genialidad de APM:

### 1. Registrar el APM

Coloca esto en tu `index.php` o un archivo `services.php` para comenzar el rastreo:

```php
use flight\apm\logger\LoggerFactory;
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
- `LoggerFactory::create()` agarra tu configuración (más sobre eso pronto) y configura un logger—SQLite por defecto.
- `Apm` es la estrella—escucha los eventos de Flight (solicitudes, rutas, errores, etc.) y recolecta métricas.
- `bindEventsToFlightInstance($app)` lo une todo a tu app de Flight.

**Consejo Pro: Muestreo**
Si tu app está ocupada, registrar *cada* solicitud podría sobrecargar las cosas. Usa una tasa de muestreo (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra el 10% de las solicitudes
```

Esto mantiene el rendimiento ágil mientras aún te da datos sólidos.

### 2. Configurarlo

Ejecuta esto para crear tu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**¿Qué hace esto?**
- Lanza un asistente que pregunta de dónde vienen las métricas crudas (fuente) y dónde va la data procesada (destino).
- Predeterminado es SQLite—p.ej., `sqlite:/tmp/apm_metrics.sqlite` para la fuente, otra para el destino.
- Terminarás con una config como:
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

> Este proceso también preguntará si quieres ejecutar las migraciones para esta configuración. Si estás configurándolo por primera vez, la respuesta es sí.

**¿Por qué dos ubicaciones?**
Las métricas crudas se acumulan rápido (piensa en logs sin filtrar). El worker las procesa en un destino estructurado para el dashboard. ¡Mantiene las cosas ordenadas!

### 3. Procesar métricas con el Worker

El worker convierte las métricas crudas en data lista para el dashboard. Ejecútalo una vez:

```bash
php vendor/bin/runway apm:worker
```

**¿Qué está haciendo?**
- Lee de tu fuente (p.ej., `apm_metrics.sqlite`).
- Procesa hasta 100 métricas (tamaño de lote predeterminado) en tu destino.
- Se detiene cuando termina o si no quedan métricas.

**Mantenerlo ejecutándose**
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
  Se dispara cada minuto—perfecto para producción.

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

Para producción, podrías tener que probar unas técnicas para hacer que el dashboard funcione, ya que probablemente hay firewalls y otras medidas de seguridad en su lugar. Aquí hay unas opciones:

- **Usa un Proxy Inverso**: Configura Nginx o Apache para reenviar solicitudes al dashboard.
- **Túnel SSH**: Si puedes SSH al servidor, usa `ssh -L 8080:localhost:8001 youruser@yourserver` para tunelizar el dashboard a tu máquina local.
- **VPN**: Si tu servidor está detrás de una VPN, conéctate a ella y accede al dashboard directamente.
- **Configura Firewall**: Abre el puerto 8001 para tu IP o la red del servidor. (o el puerto que hayas configurado).
- **Configura Apache/Nginx**: Si tienes un servidor web frente a tu aplicación, puedes configurarlo para un dominio o subdominio. Si haces esto, configurarás el document root a `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### ¿Quieres un dashboard diferente?

¡Puedes construir tu propio dashboard si quieres! Mira el directorio vendor/flightphp/apm/src/apm/presenter para ideas sobre cómo presentar la data para tu propio dashboard.

## Características del Dashboard

El dashboard es tu HQ de APM—aquí está lo que verás:

- **Log de Solicitudes**: Cada solicitud con timestamp, URL, código de respuesta y tiempo total. Haz clic en “Detalles” para middleware, consultas y errores.
- **Solicitudes Más Lentas**: Top 5 solicitudes que consumen tiempo (p.ej., “/api/heavy” en 2.5s).
- **Rutas Más Lentas**: Top 5 rutas por tiempo promedio—genial para detectar patrones.
- **Tasa de Error**: Porcentaje de solicitudes que fallan (p.ej., 2.3% 500s).
- **Percentiles de Latencia**: 95th (p95) y 99th (p99) tiempos de respuesta—conoce tus escenarios de peor caso.
- **Gráfico de Código de Respuesta**: Visualiza 200s, 404s, 500s a lo largo del tiempo.
- **Consultas/Middleware Largas**: Top 5 llamadas de base de datos lentas y capas de middleware.
- **Cache Hit/Miss**: Cuánto salva tu cache el día.

**Extras**:
- Filtra por “Última Hora”, “Último Día” o “Última Semana”.
- Cambia a modo oscuro para esas sesiones nocturnas.

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Cache: Hit en `user_list` (5ms)

## Agregando Eventos Personalizados

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
- Texto de consulta (p.ej., `SELECT * FROM users WHERE id = ?`)
- Tiempo de ejecución (p.ej., 0.015s)
- Conteo de filas (p.ej., 42)

**Atención**:
- **Opcional**: Sáltate esto si no necesitas rastreo de DB.
- **Solo PdoWrapper**: PDO core no está enganchado aún—¡mantente atento!
- **Advertencia de Rendimiento**: Registrar cada consulta en un sitio pesado en DB puede ralentizar las cosas. Usa muestreo (`$Apm = new Apm($ApmLogger, 0.1)`) para aligerar la carga.

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

Cada solicitud tiene un ID de solicitud único para rastreo. Puedes usar este ID en tu app para correlacionar logs y métricas. Por instancia, puedes agregar el ID de solicitud a una página de error:

```php
Flight::map('error', function($message) {
	// Obtén el ID de solicitud del header de respuesta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Adicionalmente podrías obtenerlo de la variable de Flight
	// Este método no funcionará bien en swoole u otras plataformas async.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Actualizando

Si estás actualizando a una versión más nueva del APM, hay una posibilidad de que haya migraciones de base de datos que necesiten ejecutarse. Puedes hacerlo ejecutando el siguiente comando:

```bash
php vendor/bin/runway apm:migrate
```
Esto ejecutará cualquier migración que sea necesaria para actualizar el esquema de la base de datos a la versión más reciente.

**Nota:** Si tu base de datos de APM es grande en tamaño, estas migraciones pueden tardar algo de tiempo en ejecutarse. Podrías querer ejecutar este comando durante horas de bajo pico.

## Purgando Datos Antiguos

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
  - ¿Está el worker ejecutándose? Verifica `ps aux | grep apm:worker`.
  - ¿Las rutas de config coinciden? Verifica que los DSNs en `.runway-config.json` apunten a archivos reales.
  - Ejecuta `php vendor/bin/runway apm:worker` manualmente para procesar métricas pendientes.

- **¿Errores en el Worker?**
  - Mira tus archivos SQLite (p.ej., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifica los logs de PHP para stack traces.

- **¿El Dashboard no Inicia?**
  - ¿Puerto 8001 en uso? Usa `--port 8080`.
  - ¿PHP no encontrado? Usa `--php-path /usr/bin/php`.
  - ¿Firewall bloqueando? Abre el puerto o usa `--host localhost`.

- **¿Demasiado Lento?**
  - Baja la tasa de muestreo: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduce el tamaño de lote: `--batch_size 20`.

- **¿No Rastrea Excepciones/Errores?**
  - Si tienes [Tracy](https://tracy.nette.org/) habilitado para tu proyecto, sobrescribirá el manejo de errores de Flight. Necesitarás deshabilitar Tracy y luego asegurarte de que `Flight::set('flight.handle_errors', true);` esté configurado.

- **¿No Rastrea Consultas de Base de Datos?**
  - Asegúrate de estar usando `PdoWrapper` para tus conexiones de base de datos.
  - Asegúrate de hacer el último argumento en el constructor `true`.