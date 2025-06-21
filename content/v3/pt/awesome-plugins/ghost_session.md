# Ghostff/Session

Gerenciador de Sessões PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional dos dados de sessão. Suporta File, MySQL, Redis e Memcached.

Clique [here](https://github.com/Ghostff/Session) para visualizar o código.

## Instalação

Instale com composer.

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

// uma coisa a lembrar é que você deve commitar sua sessão em cada carregamento de página
// ou você precisará executar auto_commit em sua configuração.
```

## Exemplo Simples

Aqui vai um exemplo simples de como você pode usar isso.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// toda vez que você escrever na sessão, deve commitá-la deliberadamente.
	$session->commit();
});

// Esta verificação poderia estar na lógica da página restrita, ou envolvida com middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faça sua lógica de página restrita aqui
});

// a versão com middleware
Flight::route('/some-restricted-page', function() {
	// lógica de página regular
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Exemplo Mais Complexo

Aqui vai um exemplo mais complexo de como você pode usar isso.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// defina um caminho personalizado para o arquivo de configuração da sessão como o primeiro argumento
// ou forneça o array personalizado
$app->register('session', Session::class, [ 
	[
		// se você quiser armazenar seus dados de sessão em um banco de dados (bom para algo como, "deslogar de todos os dispositivos" funcionalidade)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // por favor, mude isso para algo mais
		Session::CONFIG_AUTO_COMMIT   => true, // faça isso apenas se necessário e/ou se for difícil commitar() sua sessão.
												// adicionalmente, você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Driver do banco de dados para PDO dns ex(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Host do banco de dados
			'db_name'   => 'my_app_database',   # Nome do banco de dados
			'db_table'  => 'sessions',          # Tabela do banco de dados
			'db_user'   => 'root',              # Usuário do banco de dados
			'db_pass'   => '',                  # Senha do banco de dados
			'persistent_conn'=> false,          # Evite a sobrecarga de estabelecer uma nova conexão toda vez que um script precisa falar com um banco de dados, resultando em uma aplicação web mais rápida. ENCONTRE O LADO NEGATIVO VOCÊ MESMO
		]
	] 
]);
```

## Ajuda! Meus Dados de Sessão Não Estão Persistindo!

Você está definindo seus dados de sessão e eles não estão persistindo entre as solicitações? Talvez você tenha esquecido de commitar seus dados de sessão. Você pode fazer isso chamando `$session->commit()` após definir seus dados de sessão.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem-sucedido
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// toda vez que você escrever na sessão, deve commitá-la deliberadamente.
	$session->commit();
});
```

A outra forma de contornar isso é quando você configura seu serviço de sessão, você tem que definir `auto_commit` como `true` em sua configuração. Isso fará com que seus dados de sessão sejam commitados automaticamente após cada solicitação.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Adicionalmente, você poderia fazer `Flight::after('start', function() { Flight::session()->commit(); });` para commitar seus dados de sessão após cada solicitação.

## Documentação

Visite o [Github Readme](https://github.com/Ghostff/Session) para a documentação completa. As opções de configuração são [bem documentadas no arquivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) em si. O código é simples de entender se você quiser explorar este pacote você mesmo.