```pt
# Ghostff/Session

Gerenciador de Sessão em PHP (não-bloqueante, flash, segmento, encriptação de sessão). Utiliza open_ssl do PHP para opcionalmente encriptar/descriptografar os dados da sessão. Suporta Arquivo, MySQL, Redis e Memcached.

## Instalação

Instale com o composer.

```bash
composer require ghostff/session
```

## Configuração Básica

Não é necessário passar nada para usar as configurações padrão com sua sessão. Você pode ler sobre mais configurações no [Readme do Github](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight ::app();

$app->register('sessão', Session ::class);

// uma coisa a lembrar é que você deve fazer commit de sua sessão em cada carregamento de página
// ou você precisará executar auto_commit em sua configuração.
```

## Exemplo Simples

Aqui está um exemplo simples de como você poderia usar isso.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faça sua lógica de login aqui
	// valide a senha, etc.

	// se o login for bem sucedido
	$session->set('está_logado', true);
	$session->set('usuário', $usuário);

	// todas as vezes que você escrever na sessão, deve commitar deliberadamente.
	$session->commit();
});

// Esta verificação poderia estar na lógica da página restrita, ou envolvida com middleware.
Flight::route('/alguma-página-restrita', function() {
	$session = Flight::session();

	if(!$session->get('está_logado')) {
		Flight::redirect('/login');
	}

	// faça sua lógica da página restrita aqui
});

// a versão do middleware
Flight::route('/alguma-página-restrita', function() {
	// lógica regular da página
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('está_logado')) {
		Flight::redirect('/login');
	}
});
```

## Exemplo Mais Complexo

Aqui está um exemplo mais complexo de como você poderia usar isso.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight ::app();

// defina um caminho personalizado para o arquivo de configuração da sua sessão e dê a ele uma sequência aleatória para o id da sessão
$app->register('sessão', Session ::class, ['caminho/para/session_config.php', bin2hex(random_bytes(32))], function(Sessão $sessão) {
		// ou você pode substituir manualmente as opções de configuração
		$session->updateConfiguration([
			// se você quiser armazenar seus dados de sessão em um banco de dados (bom se você quiser algo como "sair de todos os dispositivos" funcional)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'meu-super-secreto-sal'), // por favor, mude isso para ser algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // só faça isso se for necessário e/ou for difícil commitar() sua sessão.
												// além disso, você poderia fazer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver de banco de dados para dns do PDO eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host do banco de dados
				'db_name'   => 'meu_banco_de_dados_do_aplicativo',   # Nome do banco de dados
				'db_table'  => 'sessões',          # Tabela do banco de dados
				'db_user'   => 'root',              # Nome de usuário do banco de dados
				'db_pass'   => '',                  # Senha do banco de dados
				'persistent_conn'=> falso,          # Evitar o overhead de estabelecer uma nova conexão toda vez que um script precisa falar com um banco de dados, resultando em um aplicativo web mais rápido. ENCONTRE O CONTRA oLADO VOCÊ MESMO
			]
		]);
	}
);
```

## Documentação

Visite o [Readme do Github](https://github.com/Ghostff/Session) para documentação completa. As opções de configuração estão [bem documentadas no arquivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) em si. O código é simples de entender se você quiser analisar este pacote você mesmo.
```