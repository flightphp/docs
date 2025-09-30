# Envoltorio JSON

## Resumen

La clase `Json` en Flight proporciona una manera simple y consistente de codificar y decodificar datos JSON en su aplicación. Envuelve las funciones JSON nativas de PHP con un mejor manejo de errores y algunos valores predeterminados útiles, haciendo que sea más fácil y seguro trabajar con JSON.

## Entendiendo

Trabajar con JSON es extremadamente común en las aplicaciones PHP modernas, especialmente al construir APIs o manejar solicitudes AJAX. La clase `Json` centraliza toda la codificación y decodificación de JSON, por lo que no tiene que preocuparse por casos extremos extraños o errores crípticos de las funciones integradas de PHP.

Características clave:
- Manejo consistente de errores (lanza excepciones en caso de fallo)
- Opciones predeterminadas para codificación/decodificación (como barras invertidas no escapadas)
- Métodos de utilidad para impresión legible y validación

## Uso Básico

### Codificando Datos a JSON

Para convertir datos PHP a una cadena JSON, use `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Salida: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Si la codificación falla, obtendrá una excepción con un mensaje de error útil.

### Impresión Legible

¿Quiere que su JSON sea legible para humanos? Use `prettyPrint()`:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### Decodificando Cadenas JSON

Para convertir una cadena JSON de vuelta a datos PHP, use `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Salida: Flight
```

Si desea un array asociativo en lugar de un objeto, pase `true` como el segundo argumento:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Salida: Flight
```

Si la decodificación falla, obtendrá una excepción con un mensaje de error claro.

### Validando JSON

Verifique si una cadena es JSON válido:

```php
if (Json::isValid($json)) {
  // ¡Es válido!
} else {
  // No es JSON válido
}
```

### Obteniendo el Último Error

Si desea verificar el último mensaje de error JSON (de las funciones nativas de PHP):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Último error JSON: $error";
}
```

## Uso Avanzado

Puede personalizar las opciones de codificación y decodificación si necesita más control (vea [opciones de json_encode de PHP](https://www.php.net/manual/en/json.constants.php)):

```php
// Codificar con la opción JSON_HEX_TAG
$json = Json::encode($data, JSON_HEX_TAG);

// Decodificar con profundidad personalizada
$data = Json::decode($json, false, 1024);
```

## Véase También

- [Collections](/learn/collections) - Para trabajar con datos estructurados que se pueden convertir fácilmente a JSON.
- [Configuration](/learn/configuration) - Cómo configurar su aplicación Flight.
- [Extending](/learn/extending) - Cómo agregar sus propias utilidades o sobrescribir clases principales.

## Solución de Problemas

- Si la codificación o decodificación falla, se lanza una excepción—envuelva sus llamadas en try/catch si desea manejar los errores de manera elegante.
- Si obtiene resultados inesperados, verifique sus datos en busca de referencias circulares o caracteres no UTF-8.
- Use `Json::isValid()` para verificar si una cadena es JSON válido antes de decodificar.

## Registro de Cambios

- v3.16.0 - Agregada la clase de utilidad de envoltorio JSON.