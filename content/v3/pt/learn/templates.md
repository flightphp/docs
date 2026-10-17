# Visualizações e Templates HTML

## Visão geral

O Flight fornece alguma funcionalidade básica de templates HTML por padrão. Templating é uma forma muito eficaz de você desacoplar a lógica da sua aplicação da sua camada de apresentação. Um mecanismo dedicado (Twig, Latte, etc.) também oferece às [ferramentas de codificação com IA](/learn/ai) uma sintaxe familiar e restrita, para que tenham menos probabilidade de despejar lógica de negócios no seu HTML.

## Compreensão

Quando você está criando uma aplicação, provavelmente terá HTML que desejará entregar de volta ao usuário final. O PHP por si só é uma linguagem de templating, mas é _muito_ fácil juntar lógica de negócios como chamadas de banco de dados, chamadas de API, etc. no seu arquivo HTML e tornar o teste e o desacoplamento um processo muito difícil. Ao inserir dados em um template e deixar o template se renderizar, fica muito mais fácil desacoplar e fazer testes unitários do seu código. Você vai nos agradecer se usar templates!

## Uso básico

O Flight permite que você substitua o mecanismo de visualização padrão simplesmente mapeando `render` (ou registrando uma classe de visualização). Role para baixo para Twig, Latte, Smarty, Blade e mais.

> **Padrão do skeleton:** O [flightphp/skeleton](https://github.com/flightphp/skeleton) oficial usa **apenas Twig** em `app/views/` (`*.twig`). Os controladores chamam `$this->app->render('welcome', $data)` (extensão opcional). Isso é uma escolha de aplicação para novos projetos — não um requisito do núcleo do Flight. Latte e outros mecanismos permanecem totalmente suportados.

### Twig

<span class="badge bg-info">padrão do skeleton</span>

[Twig](https://twig.symfony.com/) é um mecanismo de template flexível, rápido e seguro usado pelo Symfony e por muitos outros projetos PHP. Ferramentas de codificação com IA tendem a conhecer Twig especialmente bem, e ele escapa a saída automaticamente por padrão, o que ajuda a proteger contra XSS.

#### Instalação

```bash
composer require twig/twig
```

(Já incluído quando você executa `composer create-project flightphp/skeleton`.)

#### Configuração básica

Substitua o método `render` para usar Twig em vez do renderizador PHP padrão:

```php
// substitua o método render para usar Twig em vez do renderizador PHP padrão
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Onde o Twig armazena seus templates compilados
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Permite "welcome" ou "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

No skeleton, essa configuração fica em `app/config/services.php` (ambiente Twig compartilhado, caminho de cache, globais como `base_url` / nonce de CSP). Prefira injetar `Engine` e chamar `$app->render()` a partir dos controladores para que o código permaneça [favorável à IA e a testes](/learn/ai).

#### Usando Twig no Flight

Agora que você pode renderizar com Twig, pode fazer algo assim:

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

Quando você visita `/Bob` no navegador, a saída seria:

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

#### Leitura adicional

Um exemplo mais completo de uso de Twig com layouts é mostrado na seção [plugins incríveis](/awesome-plugins/twig) desta documentação. Para métricas em tempo de renderização na barra do Tracy, consulte o [painel Twig nas Extensões do Tracy](/awesome-plugins/tracy-extensions#twig-panel-optional).

Você pode aprender mais sobre todos os recursos do Twig lendo a [documentação oficial](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">ótima alternativa</span>

[Latte](https://latte.nette.org/) é um mecanismo completo com sintaxe semelhante ao PHP. Ele ainda é uma excelente escolha para aplicações Flight; o skeleton simplesmente padroniza o Twig para um padrão compartilhado (especialmente útil quando ferramentas de IA geram templates).

#### Instalação

```bash
composer require latte/latte
```

#### Configuração básica

A ideia principal é substituir o método `render` para usar Latte em vez do renderizador PHP padrão.

```php
// substitua o método render para usar latte em vez do renderizador PHP padrão
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Onde o latte armazena especificamente seu cache
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

Quando você visita `/Bob` no navegador, a saída seria:

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

#### Leitura adicional

Um exemplo mais complexo de uso de Latte com layouts é mostrado na seção [plugins incríveis](/awesome-plugins/latte) desta documentação.

Você pode aprender mais sobre todos os recursos do Latte, incluindo recursos de tradução e idioma, lendo a [documentação oficial](https://latte.nette.org/en/).

### Mecanismo de Visualização Integrado

<span class="badge bg-warning">obsoleto</span>

> **Observação:** Embora esta ainda seja a funcionalidade padrão e ainda funcione tecnicamente.

Para exibir um template de visualização, chame o método `render` com o nome do arquivo de template e dados opcionais de template:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados de template que você passa são automaticamente injetados no template e podem ser referenciados como uma variável local. Arquivos de template são simplesmente arquivos PHP. Se o conteúdo do arquivo de template `hello.php` for:

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

Observe que, ao especificar o nome do template no método render, você pode omitir a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para arquivos de template. Você pode definir um caminho alternativo para seus templates configurando o seguinte:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Por padrão, o `View` integrado do Flight também aceitará um caminho absoluto de template, ou um nome que saia desse diretório. Para a maioria das aplicações, você deve restringir isso:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Isso mantém `render()`, `fetch()` e `exists()` dentro de `flight.views.path`. Está desativado por padrão para compatibilidade retroativa. Consulte [Segurança](/learn/security#flightviewsrestrict_to_path).

#### Layouts

É comum que sites tenham um único arquivo de template de layout com conteúdo intercambiável. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional ao método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Sua visualização então terá variáveis salvas chamadas `headerContent` e `bodyContent`. Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Se os arquivos de template forem assim:

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

Aqui está como você usaria o mecanismo de template [Smarty](http://www.smarty.net/) para suas visualizações:

```php
// Carrega a biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registra o Smarty como a classe de visualização
// Também passa uma função de callback para configurar o Smarty no carregamento
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribui dados do template
Flight::view()->assign('name', 'Bob');

// Exibe o template
Flight::view()->display('hello.tpl');
```

Para completude, você também deve substituir o método render padrão do Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Aqui está como você usaria o mecanismo de template [Blade](https://laravel.com/docs/8.x/blade) para suas visualizações:

Primeiro, você precisa instalar a biblioteca BladeOne via Composer:

```bash
composer require eftec/bladeone
```

Depois, você pode configurar o BladeOne como a classe de visualização no Flight:

```php
<?php
// Carrega a biblioteca BladeOne
use eftec\bladeone\BladeOne;

// Registra o BladeOne como a classe de visualização
// Também passa uma função de callback para configurar o BladeOne no carregamento
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Atribui dados do template
Flight::view()->share('name', 'Bob');

// Exibe o template
echo Flight::view()->run('hello', []);
```

Para completude, você também deve substituir o método render padrão do Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Neste exemplo, o arquivo de template hello.blade.php pode ser assim:

```php
<?php
Hello, {{ $name }}!
```

A saída seria:

```
Hello, Bob!
```

## Veja também
- [Instalação](/install) - Layout do skeleton (`app/views/*.twig`) para novos projetos.
- [Estendendo](/learn/extending) - Como substituir o método `render` para usar um mecanismo de template diferente.
- [Roteamento](/learn/routing) - Como mapear rotas para controladores e renderizar visualizações.
- [Respostas](/learn/responses) - Como personalizar respostas HTTP.
- [Segurança](/learn/security) - Escape automático, XSS e `flight.views.restrict_to_path`.
- [IA e Experiência do Desenvolvedor](/learn/ai) - Por que um padrão de mecanismo de visualização ajuda agentes de codificação.
- [Por que um Framework?](/learn/why-frameworks) - Como os templates se encaixam no panorama geral.

## Solução de problemas
- Se você tiver um redirecionamento no seu middleware, mas seu app não parecer estar redirecionando, certifique-se de adicionar uma instrução `exit;` no seu middleware.
- Se o Twig não conseguir encontrar um template, verifique `flight.views.path` e se o arquivo existe nesse caminho com a extensão esperada (skeleton: `app/views/`).

## Histórico de alterações
- Docs – Documentado `flight.views.restrict_to_path` para visualizações PHP nativas.
- Docs – Twig documentado como o padrão oficial do skeleton; Latte continua sendo uma alternativa de primeira classe.
- v2.0 - Versão inicial.