# Detener

Puedes detener el marco de trabajo en cualquier momento llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar un código de estado `HTTP` opcional y un mensaje:

```php
Flight::halt(200, 'Vuelvo enseguida...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si deseas detener
el marco de trabajo y generar la respuesta actual, utiliza el método `stop`:

```php
Flight::stop();
```