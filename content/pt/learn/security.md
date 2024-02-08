# Segurança

Segurança é fundamental quando se trata de aplicações web. Você quer garantir que sua aplicação seja segura e que os dados dos seus usuários estejam protegidos. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web.

## Falsificação de Solicitação entre Sites (CSRF)
A Falsificação de Solicitação entre Sites (CSRF) é um tipo de ataque no qual um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para o seu site. Isso pode ser usado para realizar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF integrado, mas você pode facilmente implementar o seu próprio usando middleware.

Aqui está um exemplo de como você pode implementar a proteção CSRF usando filtros de eventos:

```php
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura o token csrf dos valores do formulário
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

## Script entre Sites (XSS)
O Script entre Sites (XSS) é um tipo de ataque no qual um site malicioso pode injetar código no seu site. A maioria dessas oportunidades vem dos valores dos formulários que os seus usuários preencherão. Você **nunca** deve confiar na saída dos seus usuários! Sempre assuma que todos eles são os melhores hackers do mundo. Eles podem injetar JavaScript ou HTML maliciosos na sua página. Esse código pode ser usado para roubar informações dos seus usuários ou realizar ações no seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para prevenir ataques XSS.

```php
// Vamos assumir que o usuário é inteligente e tenta usar isso como seu nome
$nome = '<script>alert("XSS")</script>';

// Isso escapará a saída
Flight::view()->set('nome', $nome);
// Isso vai resultar em: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também escapará automaticamente isso.
Flight::view()->render('modelo', ['nome' => $nome]);
```

## Injeção de SQL
A Injeção de SQL é um tipo de ataque no qual um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações do seu banco de dados ou realizar ações no seu banco de dados. Novamente, você nunca deve confiar na entrada dos seus usuários! Sempre assuma que eles estão com más intenções. Você pode usar declarações preparadas nos seus objetos `PDO` para prevenir a injeção de SQL.

```php
// Supondo que você tenha o Flight::db() registrado como seu objeto PDO
$declaração = Flight::db()->prepare('SELECIONE * FROM usuários WHERE nome de usuário = :nome de usuário');
$declaração->execute([':nome de usuário' => $nome de usuário]);
$usuários = $declaração->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser feito facilmente em uma linha
$usuários = Flight::db()->fetchAll('SELECIONE * FROM usuários WHERE nome de usuário = :nome de usuário', [ 'nome de usuário' => $nome de usuário ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados ?
$declaração = Flight::db()->fetchAll('SELECIONE * FROM usuários WHERE nome de usuário = ?', [ $nome de usuário ]);

// Apenas prometa que nunca JAMAIS fará algo assim...
$usuários = Flight::db()->fetchAll("SELECIONE * FROM usuários WHERE nome de usuário = '{$nome de usuário}'");
// porque e se $nome de usuário = "' OR 1=1;"; Após a consulta ser construída, parece
// assim
// SELECIONE * FROM usuários WHERE nome de usuário = '' OU 1=1;
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção SQL muito comum que retornará todos os usuários.
```

## CORS
O Compartilhamento de Recursos de Origem Cruzada (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página da web sejam solicitados a partir de outro domínio fora do domínio de onde o recurso se originou. O Flight não possui funcionalidade integrada, mas isso pode ser facilmente tratado com middleware ou filtros de eventos semelhantes ao CSRF.

```php
Flight::route('/usuários', function() {
	$usuários = Flight::db()->fetchAll('SELECIONE * FROM usuários');
	Flight::json($usuários);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## Conclusão
Segurança é fundamental e é importante garantir que suas aplicações web sejam seguras. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web, mas é importante estar sempre vigilante e garantir que você está fazendo tudo o que pode para manter os dados dos seus usuários seguros. Sempre assuma o pior e nunca confie na entrada dos seus usuários. Sempre escape a saída e use declarações preparadas para prevenir injeções de SQL. Sempre use middleware para proteger suas rotas de ataques CSRF e CORS. Se você fizer todas essas coisas, estará no caminho certo para construir aplicações web seguras.