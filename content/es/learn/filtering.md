# Filtrado

Flight te permite filtrar métodos antes y después de que sean llamados. No hay
ganchos predefinidos que necesites memorizar. Puedes filtrar cualquiera de los métodos predeterminados del framework
así como cualquier método personalizado que hayas mapeado.

Una función de filtro se ve así:

```php
function (array &$params, string &$output): bool {
  // Código de filtro
}
```

Usando las variables pasadas puedes manipular los parámetros de entrada y/o la salida.

Puedes hacer que un filtro se ejecute antes de un método de esta forma:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Haz algo
});
```

Puedes hacer que un filtro se ejecute después de un método de esta forma:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Haz algo
});
```

Puedes añadir tantos filtros como desees a cualquier método. Serán llamados en el
orden en que sean declarados.

Aquí tienes un ejemplo del proceso de filtrado:

```php
// Mapea un método personalizado
Flight::map('hello', function (string $name) {
  return "¡Hola, $name!";
});

// Añade un filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipula el parámetro
  $params[0] = 'Fred';
  return true;
});

// Añade un filtro después
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipula la salida
  $output .= " ¡Que tengas un buen día!";
  return true;
});

// Invoca el método personalizado
echo Flight::hello('Bob');
```

Esto mostrará:

```
¡Hola Fred! ¡Que tengas un buen día!
```

Si has definido múltiples filtros, puedes romper la cadena devolviendo `false`
en cualquiera de tus funciones de filtro:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'uno';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'dos';

  // Esto terminará la cadena
  return false;
});

// Esto no se llamará
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'tres';
  return true;
});
```

Nota, los métodos principales como `map` y `register` no pueden ser filtrados porque
son llamados directamente y no invocados dinámicamente.