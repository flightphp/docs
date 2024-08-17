# Ghostff/Session

Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional de dados de sessão. Suporta Arquivo, MySQL, Redis e Memcached.

Clique [aqui](https://github.com/Ghostff/Session) para visualizar o código.

## Instalação

Instale com o compositor.

```bash
composer require ghostff/session
```

## Configuração Básica

Não é necessário passar nada para usar as configurações padrão com sua sessão. Você pode ler sobre mais configurações no [README do Github] (https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// uma coisa a lembrar é que você deve confirmar sua sessão em cada carregamento de página
// ou você precisará executar auto_commit em sua configuração.
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

	// toda vez que você escreve na sessão, deve confirmá-la deliberadamente.
	$session->commit();
});

// Esta verificação pode estar na lógica da página restrita ou envolvida com middleware.
Flight::route('/alguma-pagina-restrita', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faça sua lógica de página restrita aqui
});

// a versão do middleware
Flight::route('/alguma-pagina-restrita', function() {
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

// defina um caminho personalizado para o arquivo de configuração da sessão e forneça uma string aleatória para o id da sessão
$app->register('session', Session::class, [ 'caminho/para/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou você pode substituir manualmente as opções de configuração
		$session->updateConfiguration([
			// se deseja armazenar seus dados de sessão em um banco de dados (bom se deseja algo como, "sair de todos os dispositivos" funcionalidade)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'minha-super-senha-S3CR3TA'), // por favor, mude isso para ser algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // faça isso apenas se for necessário e/ou for difícil confirmar() sua sessão.
												   // adicionalmente você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver de banco de dados para o dns do PDO, por exemplo (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host do banco de dados
				'db_name'   => 'meu_banco_de_dados_do_aplicativo',   # Nome do banco de dados
				'db_table'  => 'sessoes',          # Tabela do banco de dados
				'db_user'   => 'root',              # Nome de usuário do banco de dados
				'db_pass'   => '',                  # Senha do banco de dados
				'persistent_conn'=> false,          # Evite o overhead de estabelecer uma nova conexão toda vez que um script precisa se comunicar com um banco de dados, resultando em uma aplicação web mais rápida. ENCONTRE O LADO NEGATIVO VOCÊ MESMO
			]
		]);
	}
);
```

## Ajuda! Meus Dados de Sessão não Estão Persistindo!

Você está definindo seus dados de sessão e eles não estão persistindo entre as solicitações? Você pode ter esquecido de confirmar seus dados de sessão. Você pode fazer isso chamando `$session->commit()` depois de definir seus dados de sessão.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// validar senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// toda vez que você escreve na sessão, deve confirmá-la deliberadamente.
	$session->commit();
});
```

A outra maneira de contornar isso é ao configurar seu serviço de sessão, você tem que definir `auto_commit` como `true` em sua configuração. Isso irá confirmar automaticamente seus dados de sessão após cada solicitação.

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

Visite o [README do Github](https://github.com/Ghostff/Session) para documentação completa. As opções de configuração são [bem documentadas no arquivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) em si. O código é simples de entender se você quiser examinar este pacote por conta própria.