# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) é uma biblioteca simples para gerenciar cookies em seu aplicativo.

## Instalação

A instalação é simples com o composer.

```bash
composer require overclokk/cookie
```

## Uso

O uso é tão simples quanto registrar um novo método na classe Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Defina em seu arquivo de inicialização ou public/index.php
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Defina um cookie

		// você vai querer que isso seja falso para obter uma nova instância
		// use o comentário abaixo se quiser autocompletar
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // nome do cookie
			'1', // o valor que você deseja definir
			86400, // número de segundos que o cookie deve durar
			'/', // caminho em que o cookie estará disponível
			'example.com', // domínio em que o cookie estará disponível
			true, // o cookie só será transmitido por uma conexão HTTPS segura
			true // o cookie só estará disponível por meio do protocolo HTTP
		);

		// opcionalmente, se você quiser manter os valores padrão
		// e ter uma maneira rápida de definir um cookie por um longo tempo
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Verifique se você tem o cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// colocá-los na área do painel, por exemplo.
			Flight::redirect('/dashboard');
		}
	}
}