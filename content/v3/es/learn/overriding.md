# Anulando

Flight te permite anular su funcionalidad predeterminada para adaptarla a tus propias necesidades,
sin necesidad de modificar ningún código.

Por ejemplo, cuando Flight no puede encontrar una URL que coincida con una ruta, invoca el método `notFound`
que envía una respuesta genérica de `HTTP 404`. Puedes anular este comportamiento
utilizando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página de error 404 personalizada
  include 'errors/404.html';
});
```

Flight también te permite reemplazar componentes principales del framework.
Por ejemplo, puedes reemplazar la clase Router predeterminada con tu propia clase personalizada:

```php
// Registra tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia del enrutador, cargará tu clase
$myrouter = Flight::router();
```

Sin embargo, los métodos del framework como `map` y `register` no se pueden anular. 
Obtendrás un error si intentas hacerlo.