# Variables

Flight permite guardar variables para que puedan ser utilizadas en cualquier lugar de tu aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En otro lugar de tu aplicación
$id = Flight::get('id');
```

Para verificar si una variable ha sido establecida, puedes hacer lo siguiente:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puedes limpiar una variable haciendo:

```php
// Elimina la variable id
Flight::clear('id');

// Elimina todas las variables
Flight::clear();
```

Flight también utiliza variables con propósitos de configuración.

```php
Flight::set('flight.log_errors', true);
```