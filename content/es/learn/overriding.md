# Anulación

Flight te permite anular su funcionalidad predeterminada para adaptarla a tus propias necesidades,
sin tener que modificar ningún código.

Por ejemplo, cuando Flight no puede relacionar una URL con una ruta, invoca el método `notFound`
que envía una respuesta genérica de `HTTP 404`. Puedes anular este comportamiento
usando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página personalizada de error 404
  include 'errors/404.html';
});
```

Flight también te permite reemplazar los componentes principales del marco de trabajo.
Por ejemplo, puedes reemplazar la clase Router predeterminada con tu propia clase personalizada:

```php
// Registrar tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia del enrutador, cargará tu clase
$myrouter = Flight::router();
```

Sin embargo, los métodos del marco de trabajo como `map` y `register` no pueden ser anulados. 
Recibirás un error si intentas hacerlo.