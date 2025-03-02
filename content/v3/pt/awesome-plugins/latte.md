# Latte

[Latte](https://latte.nette.org/en/guide) é um mecanismo de modelo completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Instalação

Instale com o composer.

```bash
composer require latte/latte
```

## Configuração Básica

Existem algumas opções de configuração básicas para começar. Você pode ler mais sobre elas na [Documentação do Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Aqui é onde o Latte irá armazenar em cache seus modelos para acelerar as coisas
	// Uma coisa legal sobre o Latte é que ele atualiza automaticamente seu
	// cache quando você faz alterações nos seus modelos!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Diga ao Latte onde o diretório raiz para suas visualizações estará
	// $app->get('flight.views.path') é definido no arquivo config.php
	//   Você também pode fazer algo como `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Exemplo de Layout Simples

Aqui está um exemplo simples de um arquivo de layout. Este é o arquivo que será usado para envolver todas as suas outras visualizações.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- seus elementos de navegação aqui -->
			</nav>
		</header>
		<div id="content">
			<!-- Aqui está a mágica -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Direitos autorais
		</div>
	</body>
</html>
```

E agora temos seu arquivo que será renderizado dentro desse bloco de conteúdo:

```html
<!-- app/views/home.latte -->
<!-- Isso diz ao Latte que este arquivo está "dentro" do arquivo layout.latte -->
{extends layout.latte}

<!-- Este é o conteúdo que será renderizado dentro do layout dentro do bloco de conteúdo -->
{block content}
	<h1>Página Inicial</h1>
	<p>Bem-vindo ao meu aplicativo!</p>
{/block}
```

Então, quando você for renderizar isso dentro da sua função ou controlador, você faria algo assim:

```php
// rota simples
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Página Inicial'
	]);
});

// ou se estiver usando um controlador
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Página Inicial'
		]);
	}
}
```

Veja a [Documentação do Latte](https://latte.nette.org/en/guide) para mais informações sobre como usar o Latte em todo o seu potencial!