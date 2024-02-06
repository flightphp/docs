# Extending / Containers

Flight está diseñado para ser un marco extensible. El marco viene con un conjunto de métodos y componentes predeterminados, pero te permite mapear tus propios métodos, registrar tus propias clases o incluso anular clases y métodos existentes.

## Mapeo de Métodos

Para mapear tu propio método personalizado, utiliza la función `map`:

```php
// Mapea tu método
Flight::map('hello', function (string $name) {
  echo "¡hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

## Registro de Clases / Contenedorización

Para registrar tu propia clase, utiliza la función `register`:

```php
// Registra tu clase
Flight::register('user', User::class);

// Obtiene una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros al constructor de tu clase. Por lo tanto, al cargar tu clase personalizada, vendrá preinicializada. Puedes definir los parámetros del constructor pasando un array adicional. Aquí tienes un ejemplo de carga de una conexión de base de datos:

```php
// Registra clase con parámetros de constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtiene una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Si pasas un parámetro adicional de callback, se ejecutará inmediatamente después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu nuevo objeto. La función de callback toma un parámetro, una instancia del nuevo objeto.

```php
// El callback recibirá el objeto que fue construido
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargues tu clase obtendrás una instancia compartida. Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$compartido = Flight::db();

// Nueva instancia de la clase
$nuevo = Flight::db(false);
```

Ten en cuenta que los métodos mapeados tienen precedencia sobre las clases registradas. Si declaras ambos con el mismo nombre, solo se invocará el método mapeado.