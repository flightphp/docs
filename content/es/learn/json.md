## JSON

Vuelo proporciona soporte para el envío de respuestas JSON y JSONP. Para enviar una respuesta JSON, pasa algunos datos para que se codifiquen en JSON:

```php
Flight::json(['id' => 123]);
```

Para solicitudes JSONP, opcionalmente puedes pasar el nombre del parámetro de consulta que estás utilizando para definir tu función de devolución de llamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Por lo tanto, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se usará `jsonp` como predeterminado.