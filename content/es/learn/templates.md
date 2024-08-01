## Vistas y Plantillas HTML

Flight proporciona alguna funcionalidad básica de plantillas de forma predeterminada.

Si necesita necesidades de plantillas más complejas, consulte los ejemplos de Smarty y Latte en la sección [Vistas Personalizadas](#custom-views).

## Motor de Vistas Predeterminado

Para mostrar una plantilla de vista, llame al método `render` con el nombre
del archivo de plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pase se inyectan automáticamente en la plantilla y se
pueden hacer referencia como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el
contenido del archivo de plantilla `hello.php` es:

```php
Hola, <?= $name ?>!
```

La salida sería:

```
Hola, Bob!
```

También puede establecer manualmente variables de vista utilizando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas sus vistas. Por lo tanto, simplemente puede hacer:

```php
Flight::render('hello');
```

Tenga en cuenta que al especificar el nombre de la plantilla en el método de renderizado, puede
omitir la extensión `.php` por defecto Flight buscará un directorio `views` para archivos de plantilla. Puede
establecer una ruta alternativa para sus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/ruta/a/visitas');
```

### Diseños

Es común que los sitios web tengan un solo archivo de plantilla de diseño con contenido intercambiable.
Para renderizar el contenido que se utilizará en un diseño, puede pasar un parámetro opcional al
método `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Su vista tendrá variables guardadas llamadas `headerContent` y `bodyContent`.
Luego puede renderizar su diseño haciendo:

```php
Flight::render('layout', ['title' => 'Página de Inicio']);
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
    <title>Página de Inicio</title>
  </head>
  <body>
    <h1>Hola</h1>
    <div>Mundo</div>
  </body>
</html>
```

## Motores de Vistas Personalizadas

Flight le permite cambiar el motor de vista predeterminado simplemente registrando su
propia clase de vista.

### Smarty

Así es como usaría el motor de plantillas [Smarty](http://www.smarty.net/)
para sus vistas:

```php
// Cargar la biblioteca de Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pase una función de devolución de llamada para configurar Smarty en la carga
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Asignar datos de la plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Para completitud, también debería anular el método de renderizado predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Así es como usaría el motor de plantillas [Latte](https://latte.nette.org/)
para sus vistas:

```php
// Registrar Latte como la clase de vista
// También pase una función de devolución de llamada para configurar Latte en la carga
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aquí es donde Latte almacenará en caché sus plantillas para acelerar las cosas
	// ¡Una cosa genial sobre Latte es que actualiza automáticamente su
	// caché cuando realiza cambios en sus plantillas!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indique a Latte dónde estará el directorio raíz de sus vistas.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Y envuélvalo para que pueda usar Flight::render() correctamente
Flight::map('render', function(string $template, array $data): void {
  // Esto es como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```