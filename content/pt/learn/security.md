# Segurança

Segurança é extremamente importante quando se trata de aplicações web. Você deseja garantir que sua aplicação seja segura e que os dados de seus usuários estejam protegidos. O Flight fornece uma série de recursos para ajudá-lo a proteger suas aplicações web.

## Cabeçalhos

Os cabeçalhos HTTP são uma das formas mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para evitar clickjacking, XSS e outros ataques. Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança de seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e [observatory.mozilla.org](https://observatory.mozilla.org/).

### Adicionar Manualmente

Você pode adicionar manualmente esses cabeçalhos usando o método `header` no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para evitar clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para evitar XSS
// Observação: esse cabeçalho pode ficar muito complexo, então você vai querer
// consultar exemplos na internet para a sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para evitar XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para evitar sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar quanto informações de referência são enviadas
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Defina o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Esses podem ser adicionados no início de seus arquivos `bootstrap.php` ou `index.php`.

### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/interceptador da seguinte maneira:

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

### Adicionar como um Middleware

Você também pode adicioná-los como uma classe de middleware. Esta é uma boa maneira de manter seu código limpo e organizado.

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
// A propósito, este grupo de string vazia atua como um middleware global para
// todas as rotas. Claro, você poderia fazer a mesma coisa e adicionar
// isso apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'UserController', 'obterUsuarios' ]);
	// mais rotas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificação de Solicitação entre Sites (CSRF)

Falsificação de Solicitação entre Sites (CSRF) é um tipo de ataque em que um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para o seu site. Isso pode ser usado para realizar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo integrado de proteção contra CSRF, mas você pode facilmente implementar o seu próprio usando um middleware.

### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando o formulário for enviado.

```php
// Gerar um token CSRF e armazená-lo na sessão do usuário
// (supondo que você tenha criado um objeto de sessão e o tenha anexado ao Flight)
// Você só precisa gerar um único token por sessão (para funcionar
// em várias guias e solicitações para o mesmo usuário)
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
// Defina uma função personalizada para exibir o token CSRF
// Observação: View foi configurado com o Latte como engine de visualização
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
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturar o token csrf dos valores do formulário
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

Ou você pode usar uma classe de middleware:

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
	$router->get('/usuarios', [ 'UserController', 'obterUsuarios' ]);
	// mais rotas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) é um tipo de ataque em que um site malicioso pode injetar código em seu site. A maioria dessas oportunidades vem de valores de formulários que seus usuários preencherão. Você nunca deve confiar na saída de seus usuários! Sempre assuma que todos são os melhores hackers do mundo. Eles podem injetar JavaScript ou HTML maliciosos em sua página. Esse código pode ser usado para roubar informações de seus usuários ou realizar ações em seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para evitar ataques XSS.

```php
// Vamos assumir que o usuário é esperto e tenta usar isso como seu nome
$nome = '<script>alert("XSS")</script>';

// Isso escapará a saída
Flight::view()->set('nome', $nome);
// Isso irá gerar: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também escapará automaticamente isso.
Flight::view()->render('modelo', ['nome' => $nome]);
```

## Injeção de SQL

Injeção de SQL é um tipo de ataque em que um usuário malicioso pode injetar código SQL em seu banco de dados. Isso pode ser usado para roubar informações de seu banco de dados ou realizar ações em seu banco de dados. Novamente, você nunca deve confiar na entrada de seus usuários! Sempre assuma que eles estão atrás de você. Você pode usar instruções preparadas em seus objetos `PDO` para prevenir a injeção de SQL.

```php
// Supondo que você tenha Flight::db() registrado como seu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM usuarios WHERE nome = :nome');
$statement->execute([':nome' => $nome]);
$usuarios = $statement->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser facilmente feito em uma linha
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nome = :nome', [ 'nome' => $nome ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados ?
$statement = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nome = ?', [ $nome ]);

// Apenas prometa que nunca fará algo assim...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE nome = '{$nome}' LIMIT 5");
// porque e se $nome = "' OR 1=1; -- "; 
// Depois que a consulta for construída, ela ficará assim
// SELECT * FROM usuarios WHERE nome = '' OR 1=1; -- LIMIT 5
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção de SQL muito comum que retornará todos os usuários.
```

## CORS

Compartilhamento de Recursos de Origem Cruzada (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página da web sejam solicitados de outro domínio fora do domínio de onde o recurso se originou. O Flight não possui funcionalidades incorporadas, mas isso pode ser facilmente tratado com um gancho para ser executado antes que o método `Flight::start()` seja chamado.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// personalize seus hosts permitidos aqui.
		$permitidos = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $permitidos, true) === true) {
			$resposta = Flight::response();
			$resposta->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php ou onde quer que tenha suas rotas
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## Conclusão

Segurança é muito importante e é fundamental garantir que suas aplicações web sejam seguras. O Flight fornece uma série de recursos para ajudá-lo a proteger suas aplicações web, mas é importante sempre estar vigilante e garantir que você esteja fazendo tudo o que pode para manter os dados de seus usuários seguros. Sempre assuma o pior e nunca confie na entrada de seus usuários. Sempre escape a saída e use instruções preparadas para evitar a injeção de SQL. Sempre use middleware para proteger suas rotas contra ataques de CSRF e CORS. Se você fizer todas essas coisas, estará bem encaminhado para construir aplicações web seguras.