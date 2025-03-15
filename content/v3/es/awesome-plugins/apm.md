¡Hola! Entiendo completamente que quieras desarrollar un poco más las cosas: tus ajustes se ven sólidos, y yo construiré sobre ellos para agregar más ejemplos, descripciones y un poco de sabor adicional. Lo haré divertido y amistoso, mientras me aseguro de que esté lleno de detalles útiles para que los usuarios se sientan seguros al sumergirse. ¡Ampliemos esa página de documentación con más contexto sobre lo que hace cada parte, por qué es útil y algunos ejemplos prácticos!

Aquí tienes la versión ampliada:

---

# Documentación de FlightPHP APM

¡Bienvenido a FlightPHP APM—el entrenador personal de rendimiento de tu aplicación! Esta guía es tu hoja de ruta para configurar, usar y dominar el Monitoreo de Rendimiento de Aplicaciones (APM) con FlightPHP. Ya sea que estés buscando solicitudes lentas o simplemente quieras disfrutar de los gráficos de latencia, estamos aquí para ayudarte. ¡Hagamos que tu aplicación sea más rápida, que tus usuarios sean más felices y que tus sesiones de depuración sean un paseo!

## Por qué APM es importante

Imagina esto: tu aplicación es un restaurante ocupado. Sin una forma de rastrear cuánto tiempo tardan los pedidos o dónde se está atascando la cocina, estás adivinando por qué los clientes se van enojados. APM es tu sous-chef: observa cada paso, desde las solicitudes entrantes hasta las consultas a la base de datos, y señala cualquier cosa que te esté ralentizando. Las páginas lentas pierden usuarios (¡los estudios dicen que el 53% se van si un sitio tarda más de 3 segundos en cargar!), y APM te ayuda a detectar esos problemas *antes* de que duelan. Es tranquilidad proactiva: menos momentos de “¿por qué se rompió esto?”, más victorias de “¡mira lo bien que funciona!”.

## Instalación

Empieza con Composer:

```bash
composer require flightphp/apm
```

Necesitarás:
- **PHP 7.4+**: Nos mantiene compatibles con distribuciones de Linux LTS mientras soportamos PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: El marco ligero que estamos potenciando.

## Comenzando

Aquí tienes tu paso a paso hacia la genialidad de APM:

### 1. Registra el APM

Agrégalo a tu `index.php` o a un archivo `services.php` para comenzar a rastrear:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**¿Qué está sucediendo aquí?**
- `LoggerFactory::create()` obtiene tu configuración (más sobre eso pronto) y configura un registrador—SQLite por defecto.
- `Apm` es la estrella: escucha los eventos de Flight (solicitudes, rutas, errores, etc.) y recopila métricas.
- `bindEventsToFlightInstance($app)` lo vincula todo a tu aplicación Flight.

**Consejo Profesional: Muestreo**
Si tu aplicación está ocupada, registrar *cada* solicitud podría sobrecargar las cosas. Usa una tasa de muestreo (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra el 10% de las solicitudes
```

Esto mantiene el rendimiento ágil mientras aún te proporciona datos sólidos.

### 2. Configúralo

Ejecuta esto para crear tu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**¿Qué hace esto?**
- Inicia un asistente que pregunta de dónde provienen las métricas brutas (fuente) y a dónde va la información procesada (destino).
- El valor predeterminado es SQLite—por ejemplo, `sqlite:/tmp/apm_metrics.sqlite` para la fuente, y otro para el destino.
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

**¿Por qué dos ubicaciones?**
Las métricas brutas se acumulan rápido (piensa en registros sin filtrar). El trabajador las procesa en un destino estructurado para el panel. ¡Mantiene las cosas ordenadas!

### 3. Procesa métricas con el trabajador

El trabajador convierte métricas brutas en datos listos para el panel. Ejecútalo una vez:

```bash
php vendor/bin/runway apm:worker
```

**¿Qué está haciendo?**
- Lee de tu fuente (por ejemplo, `apm_metrics.sqlite`).
- Procesa hasta 100 métricas (tamaño de lote predeterminado) en tu destino.
- Se detiene cuando se completa o si no quedan métricas.

**Mantenlo en ejecución**
Para aplicaciones en vivo, querrás procesamiento continuo. Aquí están tus opciones:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Se ejecuta para siempre, procesando métricas a medida que llegan. Ideal para desarrollo o configuraciones pequeñas.

- **Crontab**:
  Agrega esto a tu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Se ejecuta cada minuto—perfecto para producción.

- **Tmux/Screen**:
  Inicia una sesión desacoplable:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, luego D para desacoplar; `tmux attach -t apm-worker` para reconectar
  ```
  Se mantiene en ejecución incluso si cierras sesión.

- **Ajustes personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Procesa 50 métricas a la vez.
  - `--max_messages 1000`: Detiene después de 1000 métricas.
  - `--timeout 300`: Sal de la ejecución después de 5 minutos.

**¿Por qué molestarse?**
Sin el trabajador, tu panel estará vacío. Es el puente entre los registros brutos y las percepciones accionables.

### 4. Lanza el panel

Ve los vitales de tu aplicación:

```bash
php vendor/bin/runway apm:dashboard
```

**¿Qué es esto?**
- Levanta un servidor PHP en `http://localhost:8001/apm/dashboard`.
- Muestra registros de solicitudes, rutas lentas, tasas de error, y más.

**Personalízalo**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Accesible desde cualquier IP (útil para visualización remota).
- `--port 8080`: Usa un puerto diferente si 8001 está ocupado.
- `--php-path`: Indica dónde está PHP si no está en tu PATH.

¡Accede a la URL en tu navegador y explora!

#### Modo Producción

Para producción, es posible que debas probar algunas técnicas para que el panel funcione, ya que probablemente hay cortafuegos y otras medidas de seguridad en su lugar. Aquí hay algunas opciones:

- **Usa un Proxy Inverso**: Configura Nginx o Apache para redirigir solicitudes al panel.
- **Túnel SSH**: Si puedes SSH en el servidor, usa `ssh -L 8080:localhost:8001 tuusuario@tu servidor` para tunelizar el panel a tu máquina local.
- **VPN**: Si tu servidor está detrás de una VPN, conéctate a ella y accede al panel directamente.
- **Configura el Cortafuegos**: Abre el puerto 8001 para tu IP o la red del servidor. (o cualquier puerto que hayas configurado).
- **Configura Apache/Nginx**: Si tienes un servidor web delante de tu aplicación, puedes configurarlo a un dominio o subdominio. Si haces esto, establecerás la raíz del documento en `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### ¿Quieres un panel diferente?

¡Puedes construir tu propio panel si lo deseas! Mira en el directorio vendor/flightphp/apm/src/apm/presenter para ideas sobre cómo presentar los datos para tu propio panel.

## Características del Panel

El panel es tu HQ de APM—esto es lo que verás:

- **Registro de Solicitudes**: Cada solicitud con marca de tiempo, URL, código de respuesta y tiempo total. Haz clic en “Detalles” para middleware, consultas y errores.
- **Solicitudes Más Lentas**: Las 5 solicitudes que más tiempo consumen (por ejemplo, “/api/heavy” en 2.5s).
- **Rutas Más Lentas**: Las 5 rutas por tiempo promedio—genial para detectar patrones.
- **Tasa de Error**: Porcentaje de solicitudes fallidas (por ejemplo, 2.3% 500s).
- **Percentiles de Latencia**: Tiempos de respuesta del 95% (p95) y 99% (p99)—conoce tus peores escenarios.
- **Gráfico de Códigos de Respuesta**: Visualiza los 200s, 404s, 500s con el tiempo.
- **Consultas/Llamadas Lentass**: Las 5 llamadas a la base de datos y capas de middleware más lentas.
- **Aciertos/Fallos de Caché**: La frecuencia con la que tu caché salva el día.

**Extras**:
- Filtrar por “Última Hora,” “Último Día,” o “Última Semana.”
- Cambiar a modo oscuro para esas sesiones nocturnas.

**Ejemplo**:
Una solicitud a `/users` podría mostrar:
- Tiempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Caché: Acierto en `user_list` (5ms)

## Agregando Eventos Personalizados

Rastrea cualquier cosa—como una llamada a la API o un proceso de pago:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**¿Dónde aparece?**
En los detalles de la solicitud del panel bajo “Eventos Personalizados”—expandible con un bonito formato JSON.

**Caso de Uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
¡Ahora verás si esa API está arrastrando tu aplicación hacia abajo!

## Monitoreo de Base de Datos

Rastrea consultas PDO de esta manera:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**¿Qué obtienes?**
- Texto de consulta (por ejemplo, `SELECT * FROM users WHERE id = ?`)
- Tiempo de ejecución (por ejemplo, 0.015s)
- Cuenta de filas (por ejemplo, 42)

**Advertencia**:
- **Opcional**: Omítelo si no necesitas rastrear la base de datos.
- **Solo PdoWrapper**: El núcleo de PDO aún no está conectado—¡mantente atento!
- **Advertencia de Rendimiento**: Registrar cada consulta en un sitio con muchas bases de datos puede ralentizar las cosas. Usa muestreo (`$Apm = new Apm($ApmLogger, 0.1)`) para aligerar la carga.

**Ejemplo de Salida**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tiempo: 0.023s
- Filas: 15

## Opciones del Trabajador

Ajusta el trabajador a tu gusto:

- `--timeout 300`: Detiene después de 5 minutos—bueno para pruebas.
- `--max_messages 500`: Limita a 500 métricas—mantiene las cosas finitas.
- `--batch_size 200`: Procesa 200 a la vez—equilibra velocidad y memoria.
- `--daemon`: Se ejecuta sin parar—ideal para monitoreo en vivo.

**Ejemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Se ejecuta durante una hora, procesando 100 métricas a la vez.

## Resolución de Problemas

¿Estancado? Prueba esto:

- **¿No hay datos en el panel?**
  - ¿Está en ejecución el trabajador? Verifica `ps aux | grep apm:worker`.
  - ¿Los caminos de configuración coinciden? Verifica que los DSNs de `.runway-config.json` apunten a archivos reales.
  - Ejecuta `php vendor/bin/runway apm:worker` manualmente para procesar métricas pendientes.

- **¿Errores en el trabajador?**
  - Mira tus archivos SQLite (por ejemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Revisa los registros de PHP en busca de rastros de pila.

- **¿El panel no se inicia?**
  - ¿Puerto 8001 usado? Usa `--port 8080`.
  - ¿PHP no encontrado? Usa `--php-path /usr/bin/php`.
  - ¿Cortafuegos bloqueando? Abre el puerto o usa `--host localhost`.

- **¿Demasiado lento?**
  - Reduce la tasa de muestreo: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduce el tamaño del lote: `--batch_size 20`.