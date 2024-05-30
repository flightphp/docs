# Solución de problemas

Esta página te ayudará a solucionar problemas comunes que puedas encontrar al usar Flight.

## Problemas Comunes

### 404 No Encontrado o Comportamiento de Ruta Inesperado

Si estás viendo un error 404 No Encontrado (pero juras por tu vida que realmente está allí y no es un error tipográfico) esto podría ser en realidad un problema con que estás devolviendo un valor en el punto final de tu ruta en lugar de simplemente emitirlo. La razón de esto es intencional pero podría sorprender a algunos desarrolladores.

```php
Flight::route('/hello', function(){
	// Esto podría causar un error 404 No Encontrado
	return 'Hola Mundo';
});

// Lo que probablemente deseas
Flight::route('/hello', function(){
	echo 'Hola Mundo';
});
```

La razón de esto es debido a un mecanismo especial incorporado en el enrutador que maneja la salida de retorno como una señal de "ir a la siguiente ruta". Puedes ver el comportamiento documentado en la sección de [Enrutamiento](/learn/routing#passing).