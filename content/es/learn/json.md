# JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON, pasas algunos datos para ser codificados en JSON:

```php
Flight::json(['id' => 123]);
```

Para solicitudes JSONP, opcionalmente puedes pasar el nombre del parámetro de consulta que estás utilizando para definir tu función de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se utilizará por defecto `jsonp`.