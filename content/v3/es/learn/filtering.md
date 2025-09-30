# Filtrado

## Resumen

Flight te permite filtrar [métodos mapeados](/learn/extending) antes y después de que se llamen.

## Comprensión
No hay ganchos predefinidos que necesites memorizar. Puedes filtrar cualquiera de los métodos predeterminados del framework, así como cualquier método personalizado que hayas mapeado.

Una función de filtro se ve así:

```php
/**
 * @param array $params Los parámetros pasados al método que se está filtrando.
 * @param string $output (solo v2 con búfer de salida) La salida del método que se está filtrando.
 * @return bool Devuelve true/void o no devuelvas nada para continuar la cadena, false para romper la cadena.
 */
function (array &$params, string &$output): bool {
  // Código de filtro
}
```

Usando las variables pasadas, puedes manipular los parámetros de entrada y/o la salida.

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

Puedes agregar tantos filtros como quieras a cualquier método. Se llamarán en el orden en que se declaren.

Aquí hay un ejemplo del proceso de filtrado:

```php
// Mapa un método personalizado
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Agrega un filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipula el parámetro
  $params[0] = 'Fred';
  return true;
});

// Agrega un filtro después
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipula la salida
  $output .= " Have a nice day!";
  return true;
});

// Invoca el método personalizado
echo Flight::hello('Bob');
```

Esto debería mostrar:

```
Hello Fred! Have a nice day!
```

Si has definido múltiples filtros, puedes romper la cadena devolviendo `false` en cualquiera de tus funciones de filtro:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Esto terminará la cadena
  return false;
});

// Esto no se llamará
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Nota:** Los métodos principales como `map` y `register` no se pueden filtrar porque se llaman directamente y no se invocan dinámicamente. Consulta [Extending Flight](/learn/extending) para obtener más información.

## Ver también
- [Extending Flight](/learn/extending)

## Solución de problemas
- Asegúrate de devolver `false` desde tus funciones de filtro si quieres que la cadena se detenga. Si no devuelves nada, la cadena continuará.

## Registro de cambios
- v2.0 - Lanzamiento inicial.