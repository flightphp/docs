# Vistas

Flight proporciona algunas funcionalidades básicas de plantillas de forma predeterminada.

Si necesitas necesidades de plantillas más complejas, consulta los ejemplos de Smarty y Latte en la sección de [Vistas Personalizadas](#custom-views).

Para mostrar una plantilla de vista, llama al método `render` con el nombre
del archivo de la plantilla y datos de plantilla opcionales:


```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de la plantilla que pasas se inyectan automáticamente en la plantilla y se pueden
referenciar como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el
contenido del archivo de plantilla `hello.php` es:

```php
Hola, <?= $name ?>!
```

La salida sería:

```
Hola, Bob!
```

También puedes establecer manualmente variables de vista usando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas tus vistas. Por lo tanto, simplemente puedes hacer:

```php
Flight::render('hello');
```

Ten en cuenta que al especificar el nombre de la plantilla en el método render, puedes
omitir la extensión `.php`.

Por defecto, Flight buscará un directorio `views` para archivos de plantillas. Puedes
establecer una ruta alternativa para tus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/ruta/a/vistas');
```

## Diseños

Es común que los sitios web tengan un solo archivo de plantilla de diseño con un contenido intercambiable.
Para renderizar contenido que se utilizará en un diseño, puedes pasar un parámetro opcional
al método `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Tu vista entonces tendrá variables guardadas llamadas `headerContent` y `bodyContent`.
Luego puedes renderizar tu diseño haciendo:

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

## Vistas Personalizadas

Flight te permite cambiar el motor de vistas predeterminado simplemente registrando tu
propia clase de vista.

### Smarty

Así es como usarías el motor de plantillas [Smarty](http://www.smarty.net/)
para tus vistas:

```php
// Cargar biblioteca de Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pasa una función de devolución de llamada para configurar Smarty al cargar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Asignar datos de plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Por completitud, también deberías anular el método de renderización predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Así es como usarías el motor de plantillas [Latte](https://latte.nette.org/)
para tus vistas:

```php
// Registrar Latte como la clase de vista
// También pasa una función de devolución de llamada para configurar Latte al cargar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aquí es donde Latte almacenará en caché tus plantillas para acelerar las cosas
  // ¡Una característica interesante de Latte es que actualiza automáticamente tu
  // caché cuando haces cambios en tus plantillas!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Indica a Latte cuál será el directorio raíz de tus vistas
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Y envuélvelo para que puedas usar Flight::render() correctamente
Flight::map('render', function(string $template, array $data): void {
  // Esto es como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```