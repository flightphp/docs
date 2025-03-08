# Ghostff/Session

Gerenciador de Sessões PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional de dados da sessão. Suporta File, MySQL, Redis e Memcached.

Clique [aqui](https://github.com/Ghostff/Session) para ver o código.

## Instalação

Instale com o composer.

```bash
composer require ghostff/session
```

## Configuração Básica

Você não precisa passar nada para usar as configurações padrão com sua sessão. Você pode ler sobre mais configurações no [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// uma coisa a lembrar é que você deve confirmar sua sessão em cada carregamento de página
// ou precisará executar auto_commit em sua configuração. 
```

## Exemplo Simples

Aqui está um exemplo simples de como você pode usar isso.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// a cada vez que você escreve na sessão, você deve confirmá-la deliberadamente.
	$session->commit();
});

// Esta verificação pode estar na lógica da página restrita ou encapsulada com middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faça sua lógica de página restrita aqui
});

// a versão middleware
Flight::route('/some-restricted-page', function() {
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

// defina um caminho personalizado para o seu arquivo de configuração de sessão e dê a ele uma string aleatória para o id da sessão
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou você pode sobrescrever manualmente as opções de configuração
		$session->updateConfiguration([
			// se você quiser armazenar seus dados de sessão em um banco de dados (bom se você quiser algo como "me desconectar de todos os dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // por favor, mude isto para ser algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // faça isso apenas se for necessário e/ou for difícil confirmar sua sessão.
												   // além disso, você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver do banco de dados para PDO dns eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host do banco de dados
				'db_name'   => 'my_app_database',   # Nome do banco de dados
				'db_table'  => 'sessions',          # Tabela do banco de dados
				'db_user'   => 'root',              # Nome de usuário do banco de dados
				'db_pass'   => '',                  # Senha do banco de dados
				'persistent_conn'=> false,          # Evitar a sobrecarga de estabelecer uma nova conexão toda vez que um script precisa se comunicar com um banco de dados, resultando em um aplicativo web mais rápido. ENCONTRE O LADO TRASEIRO VOCÊ MESMO
			]
		]);
	}
);
```

## Ajuda! Meus Dados de Sessão Não Estão Persistindo!

Você está definindo seus dados de sessão e eles não estão persistindo entre solicitações? Você pode ter se esquecido de confirmar seus dados de sessão. Você pode fazer isso chamando `$session->commit()` depois de definir seus dados de sessão.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// a cada vez que você escreve na sessão, você deve confirmá-la deliberadamente.
	$session->commit();
});
```

A outra maneira de contornar isso é quando você configura seu serviço de sessão, você deve definir `auto_commit` como `true` em sua configuração. Isso confirmará automaticamente seus dados de sessão após cada solicitação.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Além disso, você poderia fazer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar seus dados de sessão após cada solicitação.

## Documentação

Visite o [Github Readme](https://github.com/Ghostff/Session) para documentação completa. As opções de configuração estão [bem documentadas em default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) no próprio arquivo. O código é simples de entender se você quiser examinar este pacote por conta própria.