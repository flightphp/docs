# Extensión

Flight está diseñado para ser un marco extensible. El marco viene con un conjunto
de métodos y componentes predeterminados, pero te permite mapear tus propios métodos,
registrar tus propias clases, o incluso anular clases y métodos existentes.

Si buscas un DIC (Contenedor de Inyección de Dependencias), ve a la página de
[Contenedor de Inyección de Dependencias](dependency-injection-container).

## Mapeo de Métodos

Para mapear tu propio método personalizado simple, utiliza la función `map`:

```php
// Mapea tu método
Flight::map('hello', function (string $name) {
  echo "¡Hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

Esto se utiliza más cuando necesitas pasar variables a tu método para obtener un valor esperado.
Usar el método `register()` como se detalla abajo es más para pasar configuraciones
y luego llamar a tu clase preconfigurada.

## Registro de Clases

Para registrar tu propia clase y configurarla, utiliza la función `register`:

```php
// Registra tu clase
Flight::register('user', User::class);

// Obtén una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros al constructor de tu clase.
Entonces cuando cargas tu clase personalizada, vendrá preinicializada.
Puedes definir los parámetros del constructor pasando un array adicional.
Aquí tienes un ejemplo de carga de una conexión de base de datos:

```php
// Registra clase con parámetros de constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtén una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// y si lo necesitas más tarde en tu código, simplemente llama al mismo método de nuevo
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si pasas un parámetro de devolución de llamada adicional, se ejecutará inmediatamente
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu
nuevo objeto. La función de devolución de llamada recibe un parámetro, una instancia del nuevo objeto.

```php
// La devolución de llamada recibirá el objeto que se construyó
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargas tu clase obtendrás una instancia compartida.
Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$compartido = Flight::db();

// Nueva instancia de la clase
$nuevo = Flight::db(false);
```

Ten en cuenta que los métodos mapeados tienen prioridad sobre las clases registradas. Si
declaras ambos usando el mismo nombre, solo se invocará el método mapeado.

## Anulación de Métodos del Marco

Flight te permite anular su funcionalidad predeterminada para adaptarla a tus necesidades,
sin necesidad de modificar ningún código.

Por ejemplo, cuando Flight no puede hacer coincidir una URL con una ruta, invoca el método `notFound`
que envía una respuesta genérica de `HTTP 404`. Puedes anular este comportamiento
usando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página de error 404 personalizada
  include 'errores/404.html';
});
```

Flight también te permite reemplazar componentes básicos del marco.
Por ejemplo, puedes reemplazar la clase de Enrutador predeterminada con tu propia clase personalizada:

```php
// Registra tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia de Enrutador, cargará tu clase
$mienrutador = Flight::router();
```

Sin embargo, los métodos del marco como `map` y `register` no pueden ser anulados. Obtendrás
un error si intentas hacerlo.