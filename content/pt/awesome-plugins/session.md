# Ghostff/Session

Gestor de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional de dados de sessão. Suporta Arquivo, MySQL, Redis e Memcached.

## Instalação

Instale com o compositor.

```bash
composer require ghostff/session
```

## Configuração Básica

Você não é obrigado a passar nada para usar as configurações padrão com a sua sessão. Você pode ler mais configurações [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// uma coisa a lembrar é que você deve confirmar sua sessão em cada carregamento de página
// ou você precisará executar o auto_commit em sua configuração.
```

## Exemplo Simples

Aqui está um exemplo simples de como você pode usar isso.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// validar senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// toda vez que você escrever na sessão, você deve confirmá-la deliberadamente.
	$session->commit();
});

// Esta verificação poderia estar na lógica da página restrita, ou envolvida com middleware.
Flight::route('/alguma-página-restrita', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faça sua lógica da página restrita aqui
});

// a versão middleware
Flight::route('/alguma-página-restrita', function() {
	// lógica da página regular
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Exemplo Mais Complexo

Aqui está um exemplo mais complexo de como você pode usar isso.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// defina um caminho personalizado para o arquivo de configuração da sessão e dê a ele uma string aleatória para o id da sessão
$app->register('session', Session::class, [ 'caminho/para/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou você pode substituir manualmente as opções de configuração
		$session->updateConfiguration([
			// se você deseja armazenar seus dados de sessão em um banco de dados (bom se você quiser algo como, "me desconecte de todos os dispositivos" funcionalidade)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'minha-super-senha-S3CR3T-salt'), // por favor, mude isso para ser algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // faça apenas isso se for necessário e/ou for difícil confirmar() sua sessão.
												// adicionalmente, você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver do banco de dados para dsn do PDO, por exemplo (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host do banco de dados
				'db_name'   => 'meu_banco_de_dados_do_app',   # Nome do banco de dados
				'db_table'  => 'sessões',          # Tabela do banco de dados
				'db_user'   => 'root',              # Nome de usuário do banco de dados
				'db_pass'   => '',                  # Senha do banco de dados
				'persistent_conn'=> false,          # Evite o custo de estabelecer uma nova conexão toda vez que um script precisa conversar com um banco de dados, resultando em uma aplicação web mais rápida. ENCONTRE O LADO NEGATIVO POR SI MESMO
			]
		]);
	}
);
```

## Documentação

Visite o [Github Readme](https://github.com/Ghostff/Session) para a documentação completa. As opções de configuração estão [bem documentadas no arquivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) em si. O código é simples de entender se você quiser examinar este pacote você mesmo.