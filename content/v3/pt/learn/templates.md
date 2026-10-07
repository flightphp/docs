# Views HTML e Templates

## Visão Geral

O Flight fornece algumas funcionalidades básicas de templates HTML por padrão. Templates são uma forma muito eficaz de desconectar sua lógica de aplicação da camada de apresentação. Um mecanismo dedicado (Twig, Latte, etc.) também dá às [ferramentas de codificação de IA](/learn/ai) uma sintaxe familiar e restrita, para que sejam menos propensas a despejar lógica de negócios no seu HTML.

## Entendendo

Ao construir uma aplicação, você provavelmente terá HTML que deseja entregar de volta ao usuário final. O PHP por si só é uma linguagem de templates, mas é _muito_ fácil colocar lógica de negócios, como chamadas de banco de dados, chamadas de API, etc., dentro do seu arquivo HTML e tornar o teste e o desacoplamento um processo muito difícil. Ao empurrar dados para um template e deixar o próprio template renderizar, fica muito mais fácil desacoplar e testar unitariamente seu código. Você nos agradecerá se usar templates!

## Uso Básico

O Flight permite que você troque o mecanismo de view padrão simplesmente mapeando `render` (ou registrando uma classe de view). Role para baixo para Twig, Latte, Smarty, Blade e mais.

> **Padrão do skeleton:** O [flightphp/skeleton](https://github.com/flightphp/skeleton) oficial usa **apenas Twig** em `app/views/` (`*.twig`). Os controllers chamam `$this->app->render('welcome', $data)` (extensão opcional). Isso é uma escolha de aplicação para novos projetos—não é um requisito do núcleo do Flight. Latte e outros mecanismos continuam totalmente suportados.

### Twig

<span class="badge bg-info">padrão do skeleton</span>

[Twig](https://twig.symfony.com/) é um mecanismo de templates flexível, rápido e seguro, usado pelo Symfony e muitos outros projetos PHP. Ferramentas de codificação de IA tendem a conhecer especialmente bem o Twig, e ele escapa a saída automaticamente por padrão, o que ajuda a proteger contra XSS.

#### Instalação

```bash
composer require twig/twig
```

(Já incluído quando você executa `composer create-project flightphp/skeleton`.)

#### Configuração Básica

Sobrescreva o método `render` para usar Twig em vez do renderizador PHP padrão:

```php
// Sobrescreva o método render para usar o Twig em vez do renderizador PHP padrão
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Onde o Twig armazena seus templates compilados
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Permita "welcome" ou "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

No skeleton, essa configuração vive em `app/config/services.php` (ambiente Twig compartilhado, caminho de cache, globais como `base_url` / nonce CSP). Prefira injetar `Engine` e chamar `$app->render()` a partir dos controllers para que o código permaneça [amigável para IA e testes](/learn/ai).

#### Usando Twig no Flight

Agora que você pode renderizar com o Twig, pode fazer algo assim:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Quando você visitar `/Bob` no navegador, a saída seria:

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

Um exemplo mais completo de uso do Twig com layouts é mostrado na seção [plugins incríveis](/awesome-plugins/twig) desta documentação. Para métricas de tempo de renderização na barra do Tracy, consulte o [painel Twig nas Extensões do Tracy](/awesome-plugins/tracy-extensions#twig-panel-optional).

Você pode aprender mais sobre todos os recursos do Twig lendo a [documentação oficial](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">ótima alternativa</span>

[Latte](https://latte.nette.org/) é um mecanismo completo com sintaxe semelhante ao PHP. Ele ainda é uma excelente escolha para aplicativos Flight; o skeleton simplesmente padroniza o Twig como um único padrão compartilhado (especialmente útil quando ferramentas de IA geram templates).

#### Instalação

```bash
composer require latte/latte
```

#### Configuração Básica

A ideia principal é que você sobrescreva o método `render` para usar Latte em vez do renderizador PHP padrão.

```php
// Sobrescreva o método render para usar Latte em vez do renderizador PHP padrão
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Onde o Latte especificamente armazena seu cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Usando Latte no Flight

Agora que você pode renderizar com Latte, pode fazer algo assim:

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

Quando você visitar `/Bob` no navegador, a saída seria:

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

Um exemplo mais complexo de uso do Latte com layouts é mostrado na seção [plugins incríveis](/awesome-plugins/latte) desta documentação.

Você pode aprender mais sobre todos os recursos do Latte, incluindo capacidades de tradução e idioma, lendo a [documentação oficial](https://latte.nette.org/en/).

### Mecanismo de View Integrado

<span class="badge bg-warning">obsoleto</span>

> **Nota:** Embora esta ainda seja a funcionalidade padrão e ainda tecnicamente funcione.

Para exibir um template de view, chame o método `render` com o nome do arquivo de template e dados opcionais do template:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados de template que você passa são automaticamente injetados no template e podem ser referenciados como uma variável local. Os arquivos de template são simplesmente arquivos PHP. Se o conteúdo do arquivo de template `hello.php` for:

```php
Hello, <?= $name ?>!
```

A saída seria:

```text
Hello, Bob!
```

Você também pode definir manualmente variáveis da view usando o método `set`:

```php
Flight::view()->set('name', 'Bob');
```

A variável `name` agora está disponível em todas as suas views. Então você pode simplesmente fazer:

```php
Flight::render('hello');
```

Observe que, ao especificar o nome do template no método `render`, você pode omitir a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para os arquivos de template. Você pode definir um caminho alternativo para seus templates configurando o seguinte:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Por padrão, a `View` embutida do Flight também aceitará um caminho absoluto de template, ou um nome que saia desse diretório. Para a maioria dos aplicativos, você deve restringir isso:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Isso mantém `render()`, `fetch()` e `exists()` dentro de `flight.views.path`. Está desativado por padrão para compatibilidade com versões anteriores. Consulte [Segurança](/learn/security#flightviewsrestrict_to_path).

#### Layouts

É comum que sites tenham um único arquivo de template de layout com conteúdo intercambiável. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Sua view terá então variáveis salvas chamadas `headerContent` e `bodyContent`. Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Se os arquivos de template tiverem esta aparência:

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

Veja como você usaria o mecanismo de template [Smarty](http://www.smarty.net/) para suas views:

```php
// Carregue a biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registre o Smarty como a classe de view
// Também passe uma função de retorno para configurar o Smarty ao carregar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribua dados ao template
Flight::view()->assign('name', 'Bob');

// Exiba o template
Flight::view()->display('hello.tpl');
```

Para completude, você também deve sobrescrever o método `render` padrão do Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Veja como você usaria o mecanismo de template [Blade](https://laravel.com/docs/8.x/blade) para suas views:

Primeiro, você precisa instalar a biblioteca BladeOne via Composer:

```bash
composer require eftec/bladeone
```

Então, você pode configurar o BladeOne como a classe de view no Flight:

```php
<?php
// Carregue a biblioteca BladeOne
use eftec\bladeone\BladeOne;

// Registre o BladeOne como a classe de view
// Também passe uma função de retorno para configurar o BladeOne ao carregar
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Atribua dados ao template
Flight::view()->share('name', 'Bob');

// Exiba o template
echo Flight::view()->run('hello', []);
```

Para completude, você também deve sobrescrever o método `render` padrão do Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Neste exemplo, o arquivo de template `hello.blade.php` pode ter esta aparência:

```php
<?php
Hello, {{ $name }}!
```

A saída seria:

```
Hello, Bob!
```

## Veja Também
- [Instalação](/install) - Layout do skeleton (`app/views/*.twig`) para novos projetos.
- [Estendendo](/learn/extending) - Como sobrescrever o método `render` para usar um mecanismo de template diferente.
- [Roteamento](/learn/routing) - Como mapear rotas para controllers e renderizar views.
- [Respostas](/learn/responses) - Como personalizar respostas HTTP.
- [Segurança](/learn/security) - Auto-escaping, XSS e `flight.views.restrict_to_path`.
- [IA e Experiência do Desenvolvedor](/learn/ai) - Por que um mecanismo de view padrão ajuda agentes de codificação.
- [Por que um Framework?](/learn/why-frameworks) - Como os templates se encaixam no panorama geral.

## Solução de Problemas
- Se você tiver um redirecionamento no seu middleware, mas seu aplicativo não parece estar redirecionando, certifique-se de adicionar uma instrução `exit;` no seu middleware.
- Se o Twig não conseguir encontrar um template, verifique `flight.views.path` e se o arquivo existe nesse caminho com a extensão esperada (skeleton: `app/views/`).

## Histórico de Alterações
- Docs – Documentado `flight.views.restrict_to_path` para views PHP nativas.
- Docs – Twig documentado como o padrão oficial do skeleton; Latte continua sendo uma alternativa de primeira linha.
- v2.0 - Lançamento inicial.