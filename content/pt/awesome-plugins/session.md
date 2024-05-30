# Ghostff/Session

Gestor de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/opcional descriptografia de dados de sessão. Suporta Arquivo, MySQL, Redis e Memcached.

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

// uma coisa para lembrar é que você deve confirmar sua sessão em cada carregamento de página
// ou você precisará executar auto_commit em sua configuração.
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

	// toda vez que escrever na sessão, você deve confirmar deliberate.
	$session->commit();
});

// Esta verificação poderia estar na lógica da página restrita, ou envolta com middleware.
Flight::route('/alguma-página-restrita', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faça sua lógica da página restrita aqui
});

// a versão de middleware
Flight::route('/alguma-página-restrita', function() {
	// lógica regular da página
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

// defina um caminho personalizado para o arquivo de configuração da sua sessão e forneça uma sequência aleatória para o id da sessão
$app->register('session', Session::class, [ 'caminho/para/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou você pode substituir manualmente as opções de configuração
		$session->updateConfiguration([
			// se deseja armazenar seus dados de sessão em um banco de dados (bom se desejar algo como funcionalidade "sair de todos os dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'minha-super-senha-s3cr3ta'), // por favor, altere isso para ser algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // faça isso somente se for necessário e/ou for difícil confirmar() sua sessão.
												   // além disso, você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # driver de banco de dados para dns do PDO eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # host do banco de dados
				'db_name'   => 'nome_do_meu_banco_de_dados',   # nome do banco de dados
				'db_table'  => 'sessoes',          # tabela do banco de dados
				'db_user'   => 'root',              # nome de usuário do banco de dados
				'db_pass'   => '',                  # senha do banco de dados
				'persistent_conn'=> false,          # Evite o overhead de estabelecer uma nova conexão toda vez que um script precisa falar com um banco de dados, resultando em um aplicativo web mais rápido. ENCONTRE O LADO RUIM SOZINHO
			]
		]);
	}
);
```

## Socorro! Meus Dados de Sessão Não Estão Persistindo!

Você está definindo seus dados de sessão e eles não estão persistindo entre as solicitações? Você pode ter esquecido de confirmar os dados de sua sessão. Você pode fazer isso chamando `$session->commit()` depois de definir seus dados de sessão.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// toda vez que escrever na sessão, você deve confirmar deliberate.
	$session->commit();
});
```

Outra maneira de contornar isso é quando você configura o serviço de sessão, você deve definir `auto_commit` como `true` em sua configuração. Isso irá confirmar automaticamente seus dados de sessão após cada solicitação.

```php

$app->register('session', Session::class, [ 'caminho/para/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Além disso, você poderia fazer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar seus dados de sessão após cada solicitação.

## Documentação

Visite o [Github Readme](https://github.com/Ghostff/Session) para documentação completa. As opções de configuração estão [bem documentadas no arquivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) em si. O código é simples de entender se você quiser examinar este pacote você mesmo.