# Extendiendo / Contenedores

Flight está diseñado para ser un marco extensible. El marco viene con un conjunto
de métodos y componentes predeterminados, pero te permite mapear tus propios métodos,
registrar tus propias clases, o incluso anular las clases y métodos existentes.

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

## Registrando Clases / Contenerización

Para registrar tu propia clase, utiliza la función `register`:

```php
// Registra tu clase
Flight::register('user', User::class);

// Obtén una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros a tu constructor de clase.
Por lo tanto, cuando cargas tu clase personalizada, vendrá preinicializada.
Puedes definir los parámetros del constructor pasando un array adicional.
Aquí tienes un ejemplo de carga de una conexión de base de datos:

```php
// Registra clase con parámetros del constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtén una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Si pasas un parámetro de devolución de llamada adicional, se ejecutará inmediatamente
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu
nuevo objeto. La función de devolución de llamada toma un parámetro, una instancia del nuevo objeto.

```php
// Se pasará el objeto que fue construido a la devolución de llamada
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargas tu clase, obtendrás una instancia compartida.
Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$compartido = Flight::db();

// Nueva instancia de la clase
$nuevo = Flight::db(false);
```

Ten en cuenta que los métodos mapeados tienen precedencia sobre las clases registradas. Si
declaras ambos usando el mismo nombre, solo se invocará el método mapeado.