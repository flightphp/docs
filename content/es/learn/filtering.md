# Filtrado

Flight te permite filtrar métodos antes y después de que sean llamados. No hay ganchos predefinidos que necesites memorizar. Puedes filtrar cualquiera de los métodos predeterminados del framework, así como cualquier método personalizado que hayas mapeado.

Una función de filtro se ve así:

```php
function (array &$params, string &$output): bool {
  // Código de filtro
}
```

Utilizando las variables pasadas puedes manipular los parámetros de entrada y/o la salida.

Puedes hacer que un filtro se ejecute antes de un método haciendo:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Haz algo
});
```

Puedes hacer que un filtro se ejecute después de un método haciendo:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Haz algo
});
```

Puedes agregar tantos filtros como desees a cualquier método. Se llamarán en el orden en el que se declaren.

Aquí tienes un ejemplo del proceso de filtrado:

```php
// Mapear un método personalizado
Flight::map('hello', function (string $name) {
  return "¡Hola, $name!";
});

// Agregar un filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipular el parámetro
  $params[0] = 'Fred';
  return true;
});

// Agregar un filtro después
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipular la salida
  $output .= " ¡Que tengas un buen día!";
  return true;
});

// Invocar el método personalizado
echo Flight::hello('Bob');
```

Esto debería mostrar:

```
¡Hola Fred! ¡Que tengas un buen día!
```

Si has definido múltiples filtros, puedes romper la cadena al devolver `false` en cualquiera de tus funciones de filtro:

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

Nota que los métodos centrales como `map` y `register` no pueden ser filtrados porque se llaman directamente y no son invocados dinámicamente.