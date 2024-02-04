## Vistas

Vuelo proporciona alguna funcionalidad básica de plantillas de forma predeterminada. Para mostrar una vista de plantilla, llame al método `render` con el nombre del archivo de plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pasa se inyectan automáticamente en la plantilla y se pueden hacer referencia como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el contenido del archivo de plantilla `hello.php` es:

```php
¡Hola, <?= $name ?>!
```

La salida sería:

```
¡Hola, Bob!
```

También puede configurar manualmente variables de vista usando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas sus vistas. Entonces simplemente puede hacer:

```php
Flight::render('hello');
```

Tenga en cuenta que al especificar el nombre de la plantilla en el método de renderizado, puede omitir la extensión `.php`.

De forma predeterminada, Flight buscará un directorio `views` para archivos de plantilla. Puede establecer una ruta alternativa para sus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/ruta/a/vistas');
```

## Diseños

Es común que los sitios web tengan un único archivo de plantilla de diseño con contenido intercambiable. Para renderizar contenido que se utilizará en un diseño, puede pasar un parámetro opcional al método de `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Entonces, su vista tendrá variables guardadas llamadas `headerContent` y `bodyContent`.
Luego puede renderizar su diseño haciendo:

```php
Flight::render('layout', ['title' => 'Página de inicio']);
```

Si los archivos de plantilla se ven así:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

La salida sería:
```html
<html>
  <head>
    <title>Página de inicio</title>
  </head>
  <body>
    <h1>Hola</h1>
    <div>Mundo</div>
  </body>
</html>
```

## Vistas Personalizadas

Flight le permite cambiar el motor de vista predeterminado simplemente registrando su propia clase de vista. Así es como usaría el motor de plantillas [Smarty](http://www.smarty.net/) para sus vistas:

```php
// Cargar biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como clase de vista
// También pase una función de devolución de llamada para configurar Smarty al cargar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Asignar datos de plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Para completar, también debería anular el método de renderizado predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```