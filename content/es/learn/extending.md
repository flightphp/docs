# Extensión / Contenedores

Flight está diseñado para ser un marco extensible. El marco viene con un conjunto de métodos y componentes predeterminados, pero te permite asignar tus propios métodos, registrar tus propias clases o incluso anular clases y métodos existentes.

## Asignar Métodos

Para asignar tu propio método personalizado simple, utilizas la función `map`:

```php
// Asigna tu método
Flight::map('hello', function (string $name) {
  echo "¡hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

Esto se usa más cuando necesitas pasar variables a tu método para obtener un valor esperado. Utilizar el método `register()` como se muestra a continuación es más para pasar configuración y luego llamar a tu clase preconfigurada.

## Registro de Clases / Contenedorización

Para registrar tu propia clase y configurarla, utilizas la función `register`:

```php
// Registra tu clase
Flight::register('user', User::class);

// Obtén una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros al constructor de tu clase. Por lo tanto, al cargar tu clase personalizada, vendrá preinicializada. Puedes definir los parámetros del constructor pasando un array adicional. Aquí tienes un ejemplo de carga de una conexión a la base de datos:

```php
// Registra clase con parámetros del constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtén una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// y si lo necesitas más tarde en tu código, simplemente llama al mismo método nuevamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si pasas un parámetro de callback adicional, se ejecutará inmediatamente después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu nuevo objeto. La función de callback toma un parámetro, una instancia del nuevo objeto.

```php
// El callback recibirá el objeto que se construyó
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargas tu clase obtendrás una instancia compartida. Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$compartida = Flight::db();

// Nueva instancia de la clase
$nueva = Flight::db(false);
```

Ten en cuenta que los métodos asignados tienen precedencia sobre las clases registradas. Si declaras ambos usando el mismo nombre, solo se invocará el método asignado.

## Anulación

Flight te permite anular su funcionalidad predeterminada para adaptarse a tus propias necesidades, sin tener que modificar ningún código.

Por ejemplo, cuando Flight no puede emparejar una URL con una ruta, invoca el método `notFound` que envía una respuesta générica `HTTP 404`. Puedes anular este comportamiento utilizando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página personalizada de error 404
  include 'errors/404.html';
});
```

Flight también te permite reemplazar componentes fundamentales del marco.
Por ejemplo, puedes reemplazar la clase del enrutador predeterminado con tu propia clase personalizada:

```php
// Registra tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia del enrutador, cargará tu clase
$mienrutador = Flight::router();
```

Sin embargo, los métodos del marco como `map` y `register` no se pueden anular. Recibirás un error si intentas hacerlo.