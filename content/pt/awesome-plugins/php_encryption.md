# Criptografia PHP

[defuse/php-encryption](https://github.com/defuse/php-encryption) é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados. Eles têm um ótimo [tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md) que ajuda a explicar o básico de como usar a biblioteca, bem como importantes implicações de segurança relacionadas à criptografia.

## Instalação

A instalação é simples com o composer.

```bash
composer require defuse/php-encryption
```

## Configuração

Em seguida, você precisará gerar uma chave de criptografia.

```bash
vendor/bin/generate-defuse-key
```

 Isso vai gerar uma chave que você precisará manter em segurança. Você poderia guardar a chave em seu arquivo `app/config/config.php` no array no final do arquivo. Embora não seja o local perfeito, é pelo menos algo.

## Uso

Agora que você tem a biblioteca e uma chave de criptografia, você pode começar a criptografar e descriptografar dados.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Defina em seu arquivo de inicialização ou public/index.php
 */

// Método de criptografia
Flight::map('encrypt', function($dados_brutos) {
	$chave_criptografia = /* $config['encryption_key'] ou um file_get_contents de onde você colocou a chave */;
	return Crypto::encrypt($dados_brutos, Key::loadFromAsciiSafeString($chave_criptografia));
});

// Método de descriptografia
Flight::map('decrypt', function($dados_criptografados) {
	$chave_criptografia = /* $config['encryption_key'] ou um file_get_contents de onde você colocou a chave */;
	try {
		$dados_brutos = Crypto::decrypt($dados_criptografados, Key::loadFromAsciiSafeString($chave_criptografia));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Um ataque! Ou a chave errada foi carregada, ou o texto cifrado foi
		// alterado desde que foi criado - corrompido no banco de dados ou
		// intencionalmente modificado por Eve tentando realizar um ataque.

		// ... trate este caso de uma maneira adequada à sua aplicação ...
	}
	return $dados_brutos;
});

Flight::route('/encrypt', function() {
	$dados_criptografados = Flight::encrypt('Isto é um segredo');
	echo $dados_criptografados;
});

Flight::route('/decrypt', function() {
	$dados_criptografados = '...'; // Obtenha os dados criptografados de algum lugar
	$dados_descriptografados = Flight::decrypt($dados_criptografados);
	echo $dados_descriptografados;
});
```