# Visualizações e Modelos HTML

## Visão Geral

Flight fornece alguma funcionalidade básica de modelagem HTML por padrão. A modelagem é uma maneira muito eficaz para você desconectar a lógica da sua aplicação da camada de apresentação.

## Compreendendo

Quando você está construindo uma aplicação, provavelmente terá HTML que você desejará entregar de volta ao usuário final. PHP por si só é uma linguagem de modelagem, mas é _muito_ fácil envolver lógica de negócios como chamadas de banco de dados, chamadas de API, etc., no seu arquivo HTML e tornar o teste e o desacoplamento um processo muito difícil. Ao empurrar dados para um modelo e deixar o modelo renderizar a si mesmo, torna-se muito mais fácil desacoplar e testar unidades o seu código. Você nos agradecerá se usar modelos!

## Uso Básico

Flight permite que você troque o mecanismo de visualização padrão simplesmente registrando sua própria classe de visualização. Desça para ver exemplos de como usar Smarty, Latte, Blade e mais!

### Latte

<span class="badge bg-info">recomendado</span>

Aqui está como você usaria o mecanismo de modelo [Latte](https://latte.nette.org/)
para suas visualizações.

#### Instalação

```bash
composer require latte/latte
```

#### Configuração Básica

A ideia principal é que você sobrescreva o método `render` para usar Latte em vez do renderizador PHP padrão.

```php
// sobrescreva o método render para usar latte em vez do renderizador PHP padrão
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Onde latte armazena especificamente seu cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Usando Latte no Flight

Agora que você pode renderizar com Latte, você pode fazer algo assim:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Quando você visitar `/Bob` no seu navegador, a saída seria:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Leitura Adicional

Um exemplo mais complexo de uso do Latte com layouts é mostrado na seção de [plugins incríveis](/awesome-plugins/latte) desta documentação.

Você pode aprender mais sobre as capacidades completas do Latte, incluindo tradução e capacidades de linguagem, lendo a [documentação oficial](https://latte.nette.org/en/).

### Mecanismo de Visualização Integrado

<span class="badge bg-warning">deprecado</span>

> **Nota:** Embora isso ainda seja a funcionalidade padrão e ainda funcione tecnicamente.

Para exibir um modelo de visualização, chame o método `render` com o nome 
do arquivo de modelo e dados de modelo opcionais:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados de modelo que você passa são automaticamente injetados no modelo e podem
ser referenciados como uma variável local. Arquivos de modelo são simplesmente arquivos PHP. Se o
conteúdo do arquivo de modelo `hello.php` for:

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

Observe que ao especificar o nome do modelo no método render, você pode
deixar de fora a extensão `.php`.

Por padrão, Flight procurará um diretório `views` para arquivos de modelo. Você pode
definir um caminho alternativo para seus modelos definindo a seguinte configuração:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Layouts

É comum para sites ter um único arquivo de modelo de layout com conteúdo intercambiável.
Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro
opcional para o método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Sua visualização então terá variáveis salvas chamadas `headerContent` e `bodyContent`.
Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Se os arquivos de modelo parecerem assim:

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

### Smarty

Aqui está como você usaria o mecanismo de modelo [Smarty](http://www.smarty.net/)
para suas visualizações:

```php
// Carrega a biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registra Smarty como a classe de visualização
// Também passa uma função de callback para configurar Smarty na carga
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribui dados de modelo
Flight::view()->assign('name', 'Bob');

// Exibe o modelo
Flight::view()->display('hello.tpl');
```

Para completude, você também deve sobrescrever o método render padrão do Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Aqui está como você usaria o mecanismo de modelo [Blade](https://laravel.com/docs/8.x/blade) para suas visualizações:

Primeiro, você precisa instalar a biblioteca BladeOne via Composer:

```bash
composer require eftec/bladeone
```

Em seguida, você pode configurar BladeOne como a classe de visualização no Flight:

```php
<?php
// Carrega a biblioteca BladeOne
use eftec\bladeone\BladeOne;

// Registra BladeOne como a classe de visualização
// Também passa uma função de callback para configurar BladeOne na carga
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Atribui dados de modelo
Flight::view()->share('name', 'Bob');

// Exibe o modelo
echo Flight::view()->run('hello', []);
```

Para completude, você também deve sobrescrever o método render padrão do Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Neste exemplo, o arquivo de modelo hello.blade.php pode parecer assim:

```php
<?php
Hello, {{ $name }}!
```

A saída seria:

```
Hello, Bob!
```

## Veja Também
- [Estendendo](/learn/extending) - Como sobrescrever o método `render` para usar um mecanismo de modelo diferente.
- [Roteamento](/learn/routing) - Como mapear rotas para controladores e renderizar visualizações.
- [Respostas](/learn/responses) - Como personalizar respostas HTTP.
- [Por que um Framework?](/learn/why-frameworks) - Como os modelos se encaixam no quadro geral.

## Solução de Problemas
- Se você tiver um redirecionamento no seu middleware, mas sua aplicação não parecer estar redirecionando, certifique-se de adicionar uma declaração `exit;` no seu middleware.

## Registro de Alterações
- v2.0 - Lançamento inicial.