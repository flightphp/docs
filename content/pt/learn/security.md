# Segurança

A segurança é muito importante quando se trata de aplicações web. Você quer garantir que sua aplicação seja segura e que os dados de seus usuários estejam protegidos. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web.

## Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para evitar ataques de clickjacking, XSS e outros. Existem diversas maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança de seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e [observatory.mozilla.org](https://observatory.mozilla.org/).

### Adicionar Manualmente

Você pode adicionar manualmente esses cabeçalhos usando o método `header` no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para evitar clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para evitar XSS
// Observação: este cabeçalho pode ficar muito complexo, portanto é recomendável
//  consultar exemplos na internet para a sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para evitar XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para evitar sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar a quantidade de informações do referrer enviadas
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar o HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Defina o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser utilizados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Esses podem ser adicionados no início dos seus arquivos `bootstrap.php` ou `index.php`.

### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/interceptador como o seguinte:

```php
// Adicione os cabeçalhos em um filtro
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Adicionar como um Intermediário

Você também pode adicioná-los como uma classe intermediária. Esta é uma boa maneira de manter seu código limpo e organizado.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php ou onde quer que tenha suas rotas
// FYI, este grupo de string vazia atua como um intermediário global para
// todas as rotas. Claro, você também pode fazer o mesmo e adicionar
// isso apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new SecurityHeadersMiddleware() ]);
```

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) é um tipo de ataque em que um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para o seu site. Isso pode ser usado para executar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF integrado, mas você pode facilmente implementar o seu próprio usando um intermediário.

### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar este token em seus formulários e verificá-lo quando o formulário for enviado.

```php
// Gerar um token CSRF e armazená-lo na sessão do usuário
// (presumindo que você criou um objeto de sessão e o anexou ao Flight)
// Você só precisa gerar um único token por sessão (para que funcione
// em várias abas e solicitações para o mesmo usuário)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Use o token CSRF em seu formulário -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- outros campos do formulário -->
</form>
```

#### Usando Latte

Você também pode definir uma função personalizada para exibir o token CSRF em seus modelos Latte.

```php
// Definir uma função personalizada para exibir o token CSRF
// Observação: a visualização foi configurada com Latte como mecanismo de visualização
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Agora, em seus modelos Latte, você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

Curto e simples, não é?

### Verificar o Token CSRF

Você pode verificar o token CSRF usando filtros de evento:

```php
// Este intermediário verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
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

Ou você pode usar uma classe intermediária:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Token CSRF inválido');
			}
		}
	}
}

// index.php ou onde quer que tenha suas rotas
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) é um tipo de ataque em que um site malicioso pode injetar código no seu site. A maioria dessas oportunidades vem de valores de formulários que seus usuários preencherão. Você nunca deve confiar na saída dos seus usuários! Sempre assuma que todos são os melhores hackers do mundo. Eles podem injetar JavaScript ou HTML malicioso na sua página. Esse código pode ser usado para roubar informações dos seus usuários ou executar ações no seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para prevenir ataques XSS.

```php
// Vamos supor que o usuário seja inteligente e tente usar isso como seu nome
$nome = '<script>alert("XSS")</script>';

// Isso irá escapar a saída
Flight::view()->set('nome', $nome);
// Isso irá gerar: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também escapará automaticamente isso.
Flight::view()->render('template', ['nome' => $nome]);
```

## Injeção de SQL

A injeção de SQL é um tipo de ataque em que um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações do seu banco de dados ou executar ações no seu banco de dados. Novamente, você nunca deve confiar na entrada dos seus usuários! Sempre assuma que eles estão ferrenhos. Você pode usar declarações preparadas nos seus objetos `PDO` para prevenir a injeção de SQL.

```php
// Supondo que você tenha o Flight::db() registrado como seu objeto PDO
$declaração = Flight::db()->prepare('SELECT * FROM usuarios WHERE nome = :nome');
$declaração->execute([':nome' => $nome]);
$usuarios = $declaração->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser feito facilmente em uma linha
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nome = :nome', [ 'nome' => $nome ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados ?
$declaração = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nome = ?', [ $nome ]);

// Apenas prometa que nunca JAMAIS fará algo assim...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE nome = '{$nome}' LIMIT 5");
// porque e se $nome = "' OU 1=1; -- "; 
// Após a consulta ser montada, ela se parece com isso
// SELECT * FROM usuarios WHERE nome = '' OR 1=1; -- LIMIT 5
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção de SQL muito comum que retornará todos os usuários.
```

## CORS

Cross-Origin Resource Sharing (CORS) é um mecanismo que permite solicitar muitos recursos (por exemplo, fontes, JavaScript, etc.) de uma página web a partir de outro domínio fora do domínio do qual o recurso se originou. O Flight não possui funcionalidade integrada, mas isso pode ser tratado facilmente com um intermediário ou filtros de evento semelhante ao CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$resposta = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->permitirOrigens();
			$resposta->header('Access-Control-Allow-Credentials', true;
			$resposta->header('Access-Control-Max-Age', 86400);
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$resposta->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$resposta->header(
					"Access-Control-Allow-Headers", $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
				);
			}
			$resposta->send();
			exit(0);
		}
	}

	private function permitirOrigens(): void
	{
		// personalizar seus hosts permitidos aqui
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
			$resposta->header("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN']);
		}
	}
}

// index.php ou onde quer que tenha suas rotas
Flight::route('/usuarios', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios');
	Flight::json($usuarios);
})->addMiddleware(new CorsMiddleware());
```

## Conclusão

A segurança é muito importante e é fundamental garantir que suas aplicações web sejam seguras. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web, mas é importante estar sempre vigilante e garantir que você está fazendo tudo o que pode para manter os dados de seus usuários seguros. Sempre assuma o pior e nunca confie na entrada dos seus usuários. Sempre escape a saída e use declarações preparadas para prevenir injeção de SQL. Sempre use intermediários para proteger suas rotas contra ataques de CSRF e CORS. Se você fizer todas essas coisas, estará bem encaminhado para construir aplicações web seguras.