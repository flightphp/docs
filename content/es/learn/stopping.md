# Detener

Puedes detener el marco en cualquier punto llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar un código de estado `HTTP` y un mensaje opcional:

```php
Flight::halt(200, 'Vuelvo enseguida...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si deseas detener
el marco y mostrar la respuesta actual, utiliza el método `stop`:

```php
Flight::stop();
```