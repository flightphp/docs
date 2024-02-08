## Ampliación / Contenedores

Flight está diseñado para ser un framework extensible. El framework viene con un conjunto
de métodos y componentes por defecto, pero te permite mapear tus propios métodos,
registrar tus propias clases, o incluso anular clases y métodos existentes.

## Mapeo de Métodos

Para mapear tu propio método personalizado simple, uses la función `map`:

```php
// Mapea tu método
Flight::map('hello', function (string $name) {
  echo "¡Hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

Esto se utiliza más cuando necesitas pasar variables a tu método para obtener un
valor esperado. Usar el método `register()` como se muestra a continuación es más para pasar
configuraciones y luego llamar a tu clase preconfigurada.

## Registro de Clases / Contenedorización

Para registrar tu propia clase y configurarla, utiliza la función `register`:

```php
// Registra tu clase
Flight::register('usuario', Usuario::class);

// Obtén una instancia de tu clase
$usuario = Flight::usuario();
```

El método de registro también te permite pasar parámetros al constructor de tu clase.
Entonces, al cargar tu clase personalizada, estará preinicializada.
Puedes definir los parámetros del constructor pasando un array adicional.
Aquí tienes un ejemplo de carga de una conexión a base de datos:

```php
// Registrar clase con parámetros de constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'usuario', 'contraseña']);

// Obtén una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','usuario','contraseña');
//
$db = Flight::db();

// y si lo necesitas más tarde en tu código, simplemente llama al mismo método nuevamente
class AlgunControlador {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si pasas un parámetro de devolución de llamada adicional, se ejecutará inmediatamente
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu
nuevo objeto. La función de devolución de llamada toma un parámetro, una instancia del nuevo objeto.

```php
// La devolución de llamada recibirá el objeto que se construyó
Flight::register(
  'bd',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'usuario', 'contraseña'],
  function (PDO $bd) {
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargas tu clase, obtendrás una instancia compartida.
Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$compartido = Flight::bd();

// Nueva instancia de la clase
$nueva = Flight::bd(false);
```

Ten en cuenta que los métodos mapeados tienen precedencia sobre las clases registradas. Si
declaras ambos con el mismo nombre, solo se invocará el método mapeado.

## Anulación

Flight te permite anular su funcionalidad predeterminada para adaptarla a tus propias necesidades,
sin tener que modificar ningún código.

Por ejemplo, cuando Flight no puede encontrar una URL para una ruta, invoca el método `notFound`
que envía una respuesta genérica de `HTTP 404`. Puedes anular este comportamiento
usando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página de error personalizada 404
  include 'errores/404.html';
});
```

Flight también te permite reemplazar componentes básicos del framework.
Por ejemplo, puedes reemplazar la clase de enrutador predeterminada con tu propia clase personalizada:

```php
// Registra tu clase personalizada
Flight::register('enrutador', MiEnrutador::class);

// Cuando Flight carga la instancia de Enrutador, cargará tu clase
$miEnrutador = Flight::enrutador();
```

Sin embargo, los métodos del framework como `map` y `register` no pueden ser anulados. Si lo intentas,
obtendrás un error.