# Latte

[Latte](https://latte.nette.org/en/guide) é um motor de templates completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Instalação

Instale com o composer.

```bash
composer require latte/latte
```

## Configuração Básica

Existem algumas opções de configuração básicas para começar. Você pode ler mais sobre elas na [Documentação do Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Onde o latte armazena especificamente seu cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Exemplo Simples de Layout

Aqui está um exemplo simples de um arquivo de layout. Este é o arquivo que será usado para envolver todas as suas outras views.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Meu App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- seus elementos de navegação aqui -->
			</nav>
		</header>
		<div id="content">
			<!-- Esta é a mágica bem aqui -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

E agora temos o seu arquivo que vai ser renderizado dentro daquele bloco de conteúdo:

```html
<!-- app/views/home.latte -->
<!-- Isso diz ao Latte que este arquivo está "dentro" do arquivo layout.latte -->
{extends layout.latte}

<!-- Este é o conteúdo que será renderizado dentro do layout no bloco de conteúdo -->
{block content}
	<h1>Página Inicial</h1>
	<p>Bem-vindo ao meu app!</p>
{/block}
```

Então, quando você for renderizar isso dentro da sua função ou controlador, você faria algo assim:

```php
// rota simples
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Página Inicial'
	]);
});

// ou se você estiver usando um controlador
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Página Inicial'
		]);
	}
}
```

Consulte a [Documentação do Latte](https://latte.nette.org/en/guide) para mais informações sobre como usar o Latte em todo o seu potencial!

## Depuração com Tracy

_O PHP 8.1+ é necessário para esta seção._

Você também pode usar o [Tracy](https://tracy.nette.org/en/) para ajudar na depuração dos seus arquivos de template Latte diretamente da caixa! Se você já tiver o Tracy instalado, precisa adicionar a extensão Latte ao Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Onde o latte armazena especificamente seu cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Isso só adicionará a extensão se a Barra de Depuração do Tracy estiver ativada
	if (Debugger::$showBar === true) {
		// é aqui que você adiciona o Painel Latte ao Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```