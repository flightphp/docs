# Aprender

Esta página es una guía para aprender Flight. Cubre los conceptos básicos del framework y cómo usarlo.

## <a name="routing"></a> Enrutamiento

El enrutamiento en Flight se realiza al hacer coincidir un patrón de URL con una función de devolución de llamada.

``` php
Flight::route('/', function(){
    echo '¡hola mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que sea invocable. Así que puedes usar una función regular:

``` php
function hello(){
    echo '¡hola mundo!';
}

Flight::route('/', 'hello');
```

O un método de clase:

``` php
class Greeting {
    public static function hello() {
        echo '¡hola mundo!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

O un método de objeto:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "¡Hola, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Las rutas se emparejan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

### Enrutamiento de Métodos

Por defecto, los patrones de ruta se emparejan con todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

``` php
Flight::route('GET /', function(){
    echo 'He recibido una solicitud GET.';
});

Flight::route('POST /', function(){
    echo 'He recibido una solicitud POST.';
});
```

También puedes mapear múltiples métodos a una sola devolución de llamada usando un delimitador `|`:

``` php
Flight::route('GET|POST /', function(){
    echo 'He recibido una solicitud ya sea GET o POST.';
});
```

### Expresiones Regulares

Puedes usar expresiones regulares en tus rutas:

``` php
Flight::route('/user/[0-9]+', function(){
    // Esto coincidirá con /user/1234
});
```

### Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán a tu función de devolución de llamada.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "¡hola, $name ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros nombrados usando el delimitador `:`:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Esto coincidirá con /bob/123
    // Pero no coincidirá con /bob/12345
});
```

### Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para coincidir envolviendo segmentos en paréntesis.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Esto coincidirá con las siguientes URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Cualquier parámetro opcional que no coincida se pasará como NULL.

### comodines

La coincidencia solo se realiza en segmentos individuales de URL. Si deseas coincidir múltiples segmentos, puedes usar el comodín `*`.

``` php
Flight::route('/blog/*', function(){
    // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutear todas las solicitudes a una sola devolución de llamada, puedes hacer:

``` php
Flight::route('*', function(){
    // Hacer algo
});
```

### Paso de Ejecución

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde tu función de devolución de llamada.

``` php
Flight::route('/user/@name', function($name){
    // Verifica alguna condición
    if ($name != "Bob") {
        // Continuar a la siguiente ruta
        return true;
    }
});

Flight::route('/user/*', function(){
    // Esto será llamado
});
```

### Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que el objeto de ruta se pase a tu devolución de llamada pasando `true` como el tercer parámetro en el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu función de devolución de llamada.

``` php
Flight::route('/', function($route){
    // Array de métodos HTTP emparejados
    $route->methods;

    // Array de parámetros nombrados
    $route->params;

    // Expresión regular coincidente
    $route->regex;

    // Contiene los contenidos de cualquier '*' usado en el patrón de URL
    $route->splat;
}, true);
```
### Agrupación de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas juntas (como `/api/v1`).
Puedes hacer esto usando el método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Coincide con /api/v1/users
  });

  Flight::route('/posts', function () {
	// Coincide con /api/v1/posts
  });
});
```

Incluso puedes anidar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Coincide con POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Coincide con PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v2/users
	});
  });
});
```

#### Agrupación con Contexto de Objeto

Aún puedes usar la agrupación de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Coincide con GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Coincide con POST /api/v1/posts
  });
});
```

### Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');

// más tarde en algún lugar del código
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, supongamos que los usuarios se movieron a `/admin/users/@id` en su lugar.
Con el alias en su lugar, no tienes que cambiar en ningún lugar donde referencies el alias porque el alias ahora devolverá `/admin/users/5` como en el 
ejemplo anterior.

La aliasing de rutas también funciona en grupos:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');
});


// más tarde en algún lugar del código
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

## <a name="extending"></a> Extendiendo

Flight está diseñado para ser un framework extensible. El framework viene con un conjunto
de métodos y componentes predeterminados, pero te permite mapear tus propios métodos,
registrar tus propias clases, o incluso sobrescribir clases y métodos existentes.

### Mapeo de Métodos

Para mapear tu propio método personalizado, usas la función `map`:

``` php
// Mapea tu método
Flight::map('hello', function($name){
    echo "¡hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

### Registro de Clases

Para registrar tu propia clase, usas la función `register`:

``` php
// Registra tu clase
Flight::register('user', 'User');

// Obtiene una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros al constructor de tu clase. Así que cuando cargas tu clase personalizada, vendrá pre-inicializada.
Puedes definir los parámetros del constructor pasando un array adicional.
Aquí hay un ejemplo de cargar una conexión a la base de datos:

``` php
// Registra la clase con parámetros del constructor
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Obtiene una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Si pasas un parámetro de devolución de llamada adicional, se ejecutará inmediatamente
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu
nuevo objeto. La función de devolución de llamada toma un parámetro, una instancia del nuevo objeto.

``` php
// La devolución de llamada se pasará el objeto que fue construido
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargas tu clase obtendrás una instancia compartida.
Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

``` php
// Instancia compartida de la clase
$shared = Flight::db();

// Nueva instancia de la clase
$new = Flight::db(false);
```

Ten en cuenta que los métodos mapeados tienen precedencia sobre las clases registradas. Si declaras ambos usando el mismo nombre, solo se invocará el método mapeado.

## <a name="overriding"></a> Sobrescribir

Flight te permite sobrescribir su funcionalidad predeterminada para adaptarse a tus propias necesidades,
sin tener que modificar ningún código.

Por ejemplo, cuando Flight no puede hacer coincidir una URL con una ruta, invoca el método `notFound`
que envía una respuesta genérica `HTTP 404`. Puedes sobrescribir este comportamiento
usando el método `map`:

``` php
Flight::map('notFound', function(){
    // Muestra página 404 personalizada
    include 'errors/404.html';
});
```

Flight también te permite reemplazar componentes centrales del framework.
Por ejemplo, puedes reemplazar la clase Router predeterminada con tu propia clase personalizada:

``` php
// Registra tu clase personalizada
Flight::register('router', 'MyRouter');

// Cuando Flight carga la instancia de Router, cargará tu clase
$myrouter = Flight::router();
```

Sin embargo, los métodos del Framework como `map` y `register` no pueden ser sobrescritos. Obtendrás un error si intentas hacerlo.

## <a name="filtering"></a> Filtrado

Flight te permite filtrar métodos antes y después de que sean llamados. No hay
ganchos predefinidos que necesites memorizar. Puedes filtrar cualquier de los métodos predeterminados del framework así como cualquier método personalizado que hayas mapeado.

Una función de filtro se ve así:

``` php
function(&$params, &$output) {
    // Código de filtrado
}
```

Usando las variables pasadas puedes manipular los parámetros de entrada y/o la salida.

Puedes hacer que un filtro se ejecute antes de un método haciendo:

``` php
Flight::before('start', function(&$params, &$output){
    // Hacer algo
});
```

Puedes hacer que un filtro se ejecute después de un método haciendo:

``` php
Flight::after('start', function(&$params, &$output){
    // Hacer algo
});
```

Puedes agregar tantos filtros como desees a cualquier método. Se llamarán en el
orden en que se declaren.

Aquí hay un ejemplo del proceso de filtrado:

``` php
// Mapea un método personalizado
Flight::map('hello', function($name){
    return "¡Hola, $name!";
});

// Agrega un filtro antes
Flight::before('hello', function(&$params, &$output){
    // Manipula el parámetro
    $params[0] = 'Fred';
});

// Agrega un filtro después
Flight::after('hello', function(&$params, &$output){
    // Manipula la salida
    $output .= " ¡Que tengas un buen día!";
});

// Invoca el método personalizado
echo Flight::hello('Bob');
```

Esto debería mostrar:

``` html
¡Hola Fred! ¡Que tengas un buen día!
```

Si has definido múltiples filtros, puedes romper la cadena devolviendo `false`
en cualquiera de tus funciones de filtro:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'uno';
});

Flight::before('start', function(&$params, &$output){
    echo 'dos';

    // Esto terminará la cadena
    return false;
});

// Esto no se llamará
Flight::before('start', function(&$params, &$output){
    echo 'tres';
});
```

Ten en cuenta que los métodos centrales como `map` y `register` no pueden ser filtrados porque se
llaman directamente y no se invocan dinámicamente.

## <a name="variables"></a> Variables

Flight te permite guardar variables para que puedan usarse en cualquier parte de tu aplicación.

``` php
// Guarda tu variable
Flight::set('id', 123);

// En otra parte de tu aplicación
$id = Flight::get('id');
```
Para ver si se ha establecido una variable, puedes hacer:

``` php
if (Flight::has('id')) {
     // Hacer algo
}
```

Puedes limpiar una variable haciendo:

``` php
// Limpia la variable id
Flight::clear('id');

// Limpia todas las variables
Flight::clear();
```

Flight también utiliza variables para fines de configuración.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Vistas

Flight proporciona una funcionalidad básica de plantillas de forma predeterminada. Para mostrar una vista
de plantilla, llama al método `render` con el nombre del archivo de plantilla y datos de plantilla opcionales:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Los datos de plantilla que pasas se inyectan automáticamente en la plantilla y se pueden
referenciar como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el
contenido del archivo de plantilla `hello.php` es:

``` php
Hola, '<?php echo $name; ?>'!
```

La salida sería:

``` html
Hola, Bob!
```

También puedes establecer manualmente variables de vista utilizando el método set:

``` php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas tus vistas. Así que simplemente puedes hacer:

``` php
Flight::render('hello');
```

Ten en cuenta que al especificar el nombre de la plantilla en el método render, puedes
omitir la extensión `.php`.

Por defecto, Flight buscará un directorio `views` para archivos de plantilla. Puedes
establecer un camino alternativo para tus plantillas configurando lo siguiente:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Diseños

Es común que los sitios web tengan un solo archivo de plantilla de diseño con contenido intercambiable. Para renderizar contenido que se usará en un diseño, puedes pasar un parámetro opcional al método `render`.

``` php
Flight::render('header', array('heading' => 'Hola'), 'header_content');
Flight::render('body', array('body' => 'Mundo'), 'body_content');
```

Tu vista tendrá variables guardadas llamadas `header_content` y `body_content`.
Luego puedes renderizar tu diseño haciendo:

``` php
Flight::render('layout', array('title' => 'Página Principal'));
```

Si los archivos de plantilla lucen así:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

La salida sería:

``` html
<html>
<head>
<title>Página Principal</title>
</head>
<body>
<h1>Hola</h1>
<div>Mundo</div>
</body>
</html>
```

### Vistas Personalizadas

Flight te permite reemplazar el motor de vistas predeterminado simplemente registrando tu
propia clase de vista. Aquí te mostramos cómo usar el motor de plantillas [Smarty](http://www.smarty.net/)
para tus vistas:

``` php
// Cargar la biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pasa una función de devolución de llamada para configurar Smarty al cargar
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Asignar datos de plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Para completar, también deberías sobrescribir el método render predeterminado de Flight:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Manejo de Errores

### Errores y Excepciones

Todos los errores y excepciones son atrapados por Flight y pasados al método `error`.
El comportamiento predeterminado es enviar una respuesta genérica `HTTP 500 Internal Server Error`
con alguna información de error.

Puedes sobrescribir este comportamiento para tus propias necesidades:

``` php
Flight::map('error', function(Exception $ex){
    // Manejar error
    echo $ex->getTraceAsString();
});
```

Por defecto, los errores no se registran en el servidor web. Puedes habilitar esto cambiando la configuración:

``` php
Flight::set('flight.log_errors', true);
```

### No Encontrado

Cuando una URL no puede ser encontrada, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta `HTTP 404 Not Found` con un simple mensaje.

Puedes sobrescribir este comportamiento para tus propias necesidades:

``` php
Flight::map('notFound', function(){
    // Manejar no encontrado
});
```

## <a name="redirects"></a> Redirecciones

Puedes redirigir la solicitud actual utilizando el método `redirect` y pasando
una nueva URL:

``` php
Flight::redirect('/new/location');
```

Por defecto, Flight envía un código de estado HTTP 303. Opcionalmente puedes establecer un 
código personalizado:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, que se puede
acceder con:

``` php
$request = Flight::request();
```

El objeto de solicitud proporciona las siguientes propiedades:

``` html
url - La URL que se está solicitando
base - El subdirectorio principal de la URL
method - El método de solicitud (GET, POST, PUT, DELETE)
referrer - La URL de referencia
ip - Dirección IP del cliente
ajax - Si la solicitud es una solicitud AJAX
scheme - El protocolo del servidor (http, https)
user_agent - Información del navegador
type - El tipo de contenido
length - La longitud del contenido
query - Parámetros de cadena de consulta
data - Datos de post o datos JSON
cookies - Datos de cookies
files - Archivos subidos
secure - Si la conexión es segura
accept - Parámetros de aceptación HTTP
proxy_ip - Dirección IP del proxy del cliente
```

Puedes acceder a las propiedades `query`, `data`, `cookies` y `files`
como arrays u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

``` php
$id = Flight::request()->query['id'];
```

O puedes hacer:

``` php
$id = Flight::request()->query->id;
```

### Cuerpo de Solicitud RAW

Para obtener el cuerpo RAW de la solicitud HTTP, por ejemplo cuando se trata de solicitudes PUT, puedes hacer:

``` php
$body = Flight::request()->getBody();
```

### Entrada JSON

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}` estará disponible
desde la propiedad `data`:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Detener

Puedes detener el framework en cualquier punto llamando al método `halt`:

``` php
Flight::halt();
```

También puedes especificar un código de estado `HTTP` y un mensaje opcional:

``` php
Flight::halt(200, 'Volveré pronto...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese momento. Si deseas detener
el framework y salir la respuesta actual, utiliza el método `stop`:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> Caching HTTP

Flight proporciona soporte incorporado para caching a nivel HTTP. Si se cumple la condición de caché,
Flight devolverá una respuesta HTTP `304 Not Modified`. La próxima vez que
el cliente solicite el mismo recurso, se le pedirá que use su versión en caché local.

### Última Modificación

Puedes usar el método `lastModified` y pasar un timestamp UNIX para establecer la fecha
y la hora en que una página fue modificada por última vez. El cliente continuará usando su caché hasta
que el valor de última modificación cambie.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Este contenido será guardado en caché.';
});
```

### ETag

El caching `ETag` es similar a `Last-Modified`, excepto que puedes especificar cualquier ID que
quieras para el recurso:

``` php
Flight::route('/news', function(){
    Flight::etag('mi-id-único');
    echo 'Este contenido será guardado en caché.';
});
```

Ten en cuenta que llamar a `lastModified` o `etag` establecerá y comprobará ambos los
valores de caché. Si el valor de caché es el mismo entre solicitudes, Flight enviará inmediatamente
una respuesta `HTTP 304` y dejará de procesar.

## <a name="json"></a> JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON, 
pasas algunos datos para que sean codificados como JSON:

``` php
Flight::json(array('id' => 123));
```

Para solicitudes JSONP, puedes opcionalmente pasar el nombre del parámetro de consulta que estás 
usando para definir tu función de callback:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Así que, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

``` json
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se predeterminara a `jsonp`.

## <a name="configuration"></a> Configuración

Puedes personalizar ciertos comportamientos de Flight configurando valores de configuración
a través del método `set`.

``` php
Flight::set('flight.log_errors', true);
```

La siguiente es una lista de todas las configuraciones disponibles:

``` html 
flight.base_url - Sobrescribe la URL base de la solicitud. (default: null)
flight.case_sensitive - Coincidencia sensible a mayúsculas para URLs. (default: false)
flight.handle_errors - Permitir que Flight maneje todos los errores internamente. (default: true)
flight.log_errors - Registra errores en el archivo de log de errores del servidor web. (default: false)
flight.views.path - Directorio que contiene archivos de plantilla de vista. (default: ./views)
flight.views.extension - Extensión del archivo de plantilla de vista. (default: .php)
```

## <a name="frameworkmethods"></a> Métodos del Framework

Flight está diseñado para ser fácil de usar y entender. El siguiente es el conjunto completo
de métodos para el framework. Consiste en métodos centrales, que son métodos estáticos regulares,
y métodos extensibles, que son métodos mapeados que pueden ser filtrados
o sobrescritos.

### Métodos Centrales

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crea un método personalizado del framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra una clase en un método del framework.
Flight::before(string $name, callable $callback) // Agrega un filtro antes de un método del framework.
Flight::after(string $name, callable $callback) // Agrega un filtro después de un método del framework.
Flight::path(string $path) // Agrega una ruta para autoloading de clases.
Flight::get(string $key) // Obtiene una variable.
Flight::set(string $key, mixed $value) // Establece una variable.
Flight::has(string $key) // Verifica si una variable está establecida.
Flight::clear(array|string $key = []) // Limpia una variable.
Flight::init() // Inicializa el framework a su configuración predeterminada.
Flight::app() // Obtiene la instancia del objeto de la aplicación.
```

### Métodos Extensibles

```php
Flight::start() // Inicia el framework.
Flight::stop() // Detiene el framework y envía una respuesta.
Flight::halt(int $code = 200, string $message = '') // Detiene el framework con un código de estado y un mensaje opcionales.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Mapea un patrón de URL a una devolución de llamada.
Flight::group(string $pattern, callable $callback) // Crea agrupamientos para URLs, el patrón debe ser una cadena.
Flight::redirect(string $url, int $code) // Redirige a otra URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza caching HTTP ETag.
Flight::lastModified(int $time) // Realiza caching HTTP de última modificación.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSONP.
```

Cualquier método personalizado añadido con `map` y `register` también puede ser filtrado.

## <a name="frameworkinstance"></a> Instancia del Framework

En lugar de ejecutar Flight como una clase estática global, puedes optar por ejecutarlo
como una instancia de objeto.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo '¡hola mundo!';
});

$app->start();
```

Así que, en lugar de llamar al método estático, llamarías al método de instancia con
el mismo nombre en el objeto Engine.
