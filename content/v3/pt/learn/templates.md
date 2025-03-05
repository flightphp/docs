# Visões e Modelos HTML

O Flight fornece algumas funcionalidades básicas de modelagem por padrão.

O Flight permite que você substitua o mecanismo de visualização padrão simplesmente registrando sua própria classe de visualização. Role para baixo para ver exemplos de como usar Smarty, Latte, Blade e mais!

## Mecanismo de Visualização Integrado

Para exibir um modelo de visualização, chame o método `render` com o nome do arquivo de modelo e dados de modelo opcionais:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados do modelo que você passa são automaticamente injetados no modelo e podem ser referenciados como uma variável local. Os arquivos de modelo são simplesmente arquivos PHP. Se o conteúdo do arquivo de modelo `hello.php` for:

```php
Hello, <?= $name ?>!
```

A saída seria:

```text
Hello, Bob!
```

Você também pode definir variáveis de visualização manualmente usando o método set:

```php
Flight::view()->set('name', 'Bob');
```

A variável `name` agora está disponível em todas as suas visualizações. Então você pode simplesmente fazer:

```php
Flight::render('hello');
```

Observe que ao especificar o nome do modelo no método render, você pode deixar de fora a extensão `.php`.

Por padrão, o Flight irá procurar um diretório `views` para arquivos de modelo. Você pode definir um caminho alternativo para seus modelos configurando o seguinte:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

É comum que websites tenham um único arquivo de modelo de layout com conteúdo intercambiável. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Sua visualização terá então variáveis salvas chamadas `headerContent` e `bodyContent`. Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Se os arquivos de modelo forem assim:

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

A saída seria:
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## Smarty

Aqui está como você usaria o [Smarty](http://www.smarty.net/)
mecanismo de modelo para suas visualizações:

```php
// Carregar biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como a classe de visualização
// Também passe uma função de retorno para configurar o Smarty ao carregar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribuir dados do modelo
Flight::view()->assign('name', 'Bob');

// Exibir o modelo
Flight::view()->display('hello.tpl');
```

Para completar, você também deve sobrescrever o método render padrão do Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Aqui está como você usaria o [Latte](https://latte.nette.org/)
mecanismo de modelo para suas visualizações:

```php
// Registrar Latte como a classe de visualização
// Também passe uma função de retorno para configurar o Latte ao carregar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aqui é onde o Latte irá armazenar seus modelos para acelerar as coisas
	// Uma coisa interessante sobre o Latte é que ele atualiza automaticamente seu
	// cache quando você faz alterações em seus modelos!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Diga ao Latte onde estará o diretório raiz para suas visualizações.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// E finalize para que você possa usar Flight::render() corretamente
Flight::map('render', function(string $template, array $data): void {
  // Isso é como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Aqui está como você usaria o [Blade](https://laravel.com/docs/8.x/blade) mecanismo de modelo para suas visualizações:

Primeiro, você precisa instalar a biblioteca BladeOne via Composer:

```bash
composer require eftec/bladeone
```

Em seguida, você pode configurar o BladeOne como a classe de visualização no Flight:

```php
<?php
// Carregar biblioteca BladeOne
use eftec\bladeone\BladeOne;

// Registrar BladeOne como a classe de visualização
// Também passe uma função de retorno para configurar o BladeOne ao carregar
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Atribuir dados do modelo
Flight::view()->share('name', 'Bob');

// Exibir o modelo
echo Flight::view()->run('hello', []);
```

Para completar, você também deve sobrescrever o método render padrão do Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Neste exemplo, o arquivo de modelo hello.blade.php pode ser assim:

```php
<?php
Hello, {{ $name }}!
```

A saída seria:

```
Hello, Bob!
```

Seguindo esses passos, você pode integrar o mecanismo de modelo Blade com o Flight e usá-lo para renderizar suas visualizações.