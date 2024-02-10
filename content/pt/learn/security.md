# Segurança

Segurança é uma questão importante quando se trata de aplicações web. Você deseja garantir que sua aplicação seja segura e que os dados de seus usuários estejam protegidos. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web.

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) é um tipo de ataque no qual um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para o seu site. Isso pode ser usado para realizar ações em seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF integrado, mas você pode facilmente implementar o seu próprio usando middleware.

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificar quando o formulário é enviado.

```php
// Gerar um token CSRF e armazená-lo na sessão do usuário
// (supondo que você criou um objeto de sessão e o anexou ao Flight)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Use o token CSRF em seu formulário -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- outros campos do formulário -->
</form>
```

E então você pode verificar o token CSRF usando filtros de evento:

```php
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura o token csrf dos valores do formulário
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) é um tipo de ataque no qual um site malicioso pode injetar código em seu site. A maioria dessas oportunidades vem dos valores de formulário que seus usuários preencherão. Você nunca deve confiar na saída de seus usuários! Sempre assuma que todos eles são os melhores hackers do mundo. Eles podem injetar JavaScript ou HTML maliciosos em sua página. Esse código pode ser usado para roubar informações de seus usuários ou realizar ações em seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para prevenir ataques XSS.

```php

// Vamos supor que o usuário é inteligente e tenta usar isso como seu nome
$nome = '<script>alert("XSS")</script>';

// Isso irá escapar a saída
Flight::view()->set('name', $name);
// Isso irá produzir: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também irá escapar automaticamente.
Flight::view()->render('template', ['name' => $name]);
```

## Injeção de SQL

Injeção de SQL é um tipo de ataque no qual um usuário malicioso pode injetar código SQL em seu banco de dados. Isso pode ser usado para roubar informações de seu banco de dados ou realizar ações em seu banco de dados. Novamente, você nunca deve confiar na entrada de seus usuários! Sempre assuma que eles estão à procura de problemas. Você pode usar declarações preparadas em seus objetos `PDO` para prevenir a injeção de SQL.

```php

// Supondo que você tenha Flight::db() registrado como seu objeto PDO
$declaracao = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$declaracao->execute([':username' => $username]);
$usuarios = $declaracao->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser feito facilmente em uma linha
$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados ?
$declaracao = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Apenas prometa que nunca FARÁ algo assim...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque e se $username = "' OR 1=1; -- "; Após a consulta ser construída, ela fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Isso parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque muito comum de injeção de SQL que retornará todos os usuários.
```

## CORS

Cross-Origin Resource Sharing (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página da web sejam solicitados de outro domínio fora do domínio de origem do recurso. O Flight não possui funcionalidade incorporada, mas isso pode ser facilmente tratado com middleware ou filtros de evento, semelhante ao CSRF.

```php

// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$resposta = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$resposta->header('Access-Control-Allow-Credentials: true');
			$resposta->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$resposta->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$resposta->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$resposta->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		$permitido = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $permitido)) {
			$resposta = Flight::response();
			$resposta->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php ou onde você tiver suas rotas
Flight::route('/users', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($usuarios);
})->addMiddleware(new CorsMiddleware());
```

## Conclusão

Segurança é uma questão importante e é fundamental garantir que suas aplicações web sejam seguras. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web, mas é importante estar sempre atento e garantir que você esteja fazendo tudo o que pode para manter os dados de seus usuários seguros. Sempre assuma o pior e nunca confie na entrada de seus usuários. Sempre escape a saída e use declarações preparadas para prevenir injeções de SQL. Sempre use middleware para proteger suas rotas de ataques CSRF e CORS. Se você fizer todas essas coisas, estará no caminho certo para construir aplicações web seguras.