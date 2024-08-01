# Vistas HTML y Plantillas

Flight proporciona por defecto alguna funcionalidad básica de plantillas.

Si necesita necesidades de plantillas más complejas, consulte los ejemplos de Smarty y Latte en la sección de [Vistas Personalizadas](#custom-views).

## Motor de Vistas por Defecto

Para mostrar una plantilla de vista, llame al método `render` con el nombre
del archivo de la plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pasa se inyectan automáticamente en la plantilla y se pueden
hacer referencia como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el
contenido del archivo de plantilla `hello.php` es:

```php
¡Hola, <?= $name ?>!
```

La salida sería:

```
¡Hola, Bob!
```

También puede establecer manualmente las variables de vista utilizando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas sus vistas. Entonces simplemente puede hacer:

```php
Flight::render('hello');
```

Tenga en cuenta que al especificar el nombre de la plantilla en el método de representación, puede
omitir la extensión `.php`.

De forma predeterminada, Flight buscará un directorio `views` para archivos de plantilla. Puede
establecer una ruta alternativa para sus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/ruta/a/views');
```

### Diseños

Es común que los sitios web tengan un solo archivo de plantilla de diseño con contenido intercambiable.
Para renderizar contenido que se utilizará en un diseño, puede pasar un parámetro opcional al método `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Entonces su vista tendrá variables guardadas llamadas `headerContent` y `bodyContent`.
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
    <?= $heaterContent ?>
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

## Motores de Vistas Personalizadas

Flight le permite cambiar el motor de vista predeterminado simplemente registrando su
propia clase de vista.

### Smarty

Así es como usaría el [Smarty](http://www.smarty.net/)
motor de plantillas para sus vistas:

```php
// Cargar biblioteca de Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pase una función de devolución de llamada para configurar Smarty al cargar
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

Para completar, también debe anular el método de representación predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Así es como usaría el [Latte](https://latte.nette.org/)
motor de plantillas para sus vistas:

```php

// Registrar Latte como la clase de vista
// También pase una función de devolución de llamada para configurar Latte al cargar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aquí es donde Latte almacenará en caché sus plantillas para acelerar las cosas
  // ¡Una característica interesante de Latte es que actualiza automáticamente su
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