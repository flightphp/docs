# Variables

Vuelo te permite guardar variables para que puedan ser utilizadas en cualquier lugar de tu aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En otro lugar de tu aplicación
$id = Flight::get('id');
```
Para ver si una variable ha sido establecida puedes hacer:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puedes borrar una variable haciendo:

```php
// Borra la variable id
Flight::clear('id');

// Borra todas las variables
Flight::clear();
```

Vuelo también utiliza variables con propósitos de configuración.

```php
Flight::set('flight.log_errors', true);
```