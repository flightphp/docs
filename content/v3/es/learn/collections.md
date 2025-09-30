# Colecciones

## Resumen

La clase `Collection` en Flight es una utilidad práctica para gestionar conjuntos de datos. Te permite acceder y manipular datos utilizando tanto notación de array como de objeto, haciendo que tu código sea más limpio y flexible.

## Entendiendo

Una `Collection` es básicamente un envoltorio alrededor de un array, pero con algunos poderes extra. Puedes usarla como un array, iterar sobre ella, contar sus elementos e incluso acceder a los elementos como si fueran propiedades de un objeto. Esto es especialmente útil cuando quieres pasar datos estructurados en tu aplicación, o cuando quieres hacer que tu código sea un poco más legible.

Las colecciones implementan varias interfaces de PHP:
- `ArrayAccess` (para que puedas usar sintaxis de array)
- `Iterator` (para que puedas iterar con `foreach`)
- `Countable` (para que puedas usar `count()`)
- `JsonSerializable` (para que puedas convertir fácilmente a JSON)

## Uso Básico

### Creando una Colección

Puedes crear una colección simplemente pasando un array a su constructor:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Accediendo a Elementos

Puedes acceder a los elementos utilizando notación de array o de objeto:

```php
// Notación de array
echo $collection['name']; // Salida: FlightPHP

// Notación de objeto
echo $collection->version; // Salida: 3
```

Si intentas acceder a una clave que no existe, obtendrás `null` en lugar de un error.

### Estableciendo Elementos

Puedes establecer elementos utilizando cualquiera de las dos notaciones:

```php
// Notación de array
$collection['author'] = 'Mike Cao';

// Notación de objeto
$collection->license = 'MIT';
```

### Verificando y Eliminando Elementos

Verifica si un elemento existe:

```php
if (isset($collection['name'])) {
  // Haz algo
}

if (isset($collection->version)) {
  // Haz algo
}
```

Elimina un elemento:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterando Sobre una Colección

Las colecciones son iterables, por lo que puedes usarlas en un bucle `foreach`:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Contando Elementos

Puedes contar el número de elementos en una colección:

```php
echo count($collection); // Salida: 4
```

### Obteniendo Todas las Claves o Datos

Obtén todas las claves:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Obtén todos los datos como un array:

```php
$data = $collection->getData();
```

### Limpiando la Colección

Elimina todos los elementos:

```php
$collection->clear();
```

### Serialización JSON

Las colecciones se pueden convertir fácilmente a JSON:

```php
echo json_encode($collection);
// Salida: {"name":"FlightPHP","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Uso Avanzado

Puedes reemplazar completamente el array de datos interno si es necesario:

```php
$collection->setData(['foo' => 'bar']);
```

Las colecciones son especialmente útiles cuando quieres pasar datos estructurados entre componentes, o cuando quieres proporcionar una interfaz más orientada a objetos para datos de array.

## Ver También

- [Requests](/learn/requests) - Aprende cómo manejar solicitudes HTTP y cómo las colecciones se pueden usar para gestionar datos de solicitud.
- [PDO Wrapper](/learn/pdo-wrapper) - Aprende cómo usar el envoltorio PDO en Flight y cómo las colecciones se pueden usar para gestionar resultados de base de datos.

## Solución de Problemas

- Si intentas acceder a una clave que no existe, obtendrás `null` en lugar de un error.
- Recuerda que las colecciones no son recursivas: los arrays anidados no se convierten automáticamente a colecciones.
- Si necesitas restablecer la colección, usa `$collection->clear()` o `$collection->setData([])`.

## Registro de Cambios

- v3.0 - Mejoras en las sugerencias de tipo y soporte para PHP 8+.
- v1.0 - Lanzamiento inicial de la clase Collection.