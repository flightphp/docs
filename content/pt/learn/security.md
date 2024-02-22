# Segurança

A segurança é de extrema importância quando se trata de aplicações web. Você quer garantir que sua aplicação seja segura e que os dados de seus usuários estejam protegidos. O Flight fornece várias funcionalidades para ajudar a proteger suas aplicações web.

## Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para evitar ataques de clickjacking, XSS e outros. Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança de seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e [observatory.mozilla.org](https://observatory.mozilla.org/).

### Adicionar Manualmente

Você pode adicionar manualmente esses cabeçalhos usando o método 'header' no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para evitar clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para evitar XSS
// Observação: este cabeçalho pode se tornar muito complexo, então é melhor
//  consultar exemplos na internet para a sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para evitar XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para evitar sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar quanto de informação de referência é enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Defina o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```
Esses podem ser adicionados no início de seus arquivos `bootstrap.php` ou `index.php`.

### Adicionar como Filtro

Você também pode adicioná-los em um filtro/gatilho da seguinte forma: 

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

### Adicionar como Middleware

Você também pode adicioná-los como uma classe de middleware. Essa é uma boa maneira de manter seu código limpo e organizado.

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

// index.php ou onde você tem suas rotas
// Observação: este grupo de string vazia atua como um middleware global para
// todas as rotas. Claro, você poderia fazer a mesma coisa e adicionar
// isso apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificação de Solicitação entre Sites (CSRF)

A Falsificação de Solicitação entre Sites (CSRF) é um tipo de ataque em que um site malicioso pode fazer o navegador do usuário enviar uma solicitação para o seu site. Isso pode ser usado para executar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF integrado, mas você pode implementar facilmente o seu próprio usando um middleware.

### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando o formulário for enviado.

```php
// Gerar um token CSRF e armazená-lo na sessão do usuário
// (assumindo que você criou um objeto de sessão e o anexou ao Flight)
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

#### Usando o Latte

Você também pode configurar uma função personalizada para exibir o token CSRF em seus templates Latte.

```php
// Configurar uma função personalizada para exibir o token CSRF
// Observação: a Visualização foi configurada com o Latte como mecanismo de visualização
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Agora em seus templates Latte, você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

Rápido e simples, certo?

### Verificar o Token CSRF

Você pode verificar o token CSRF usando filtros de eventos:

```php
// Este middleware verifica se a solicitação é do tipo POST e, se for, verifica se o token CSRF é válido
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

// index.php ou onde você tem suas rotas
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new CsrfMiddleware() ]);
```


## Scripts entre Sites (XSS)

Scripts entre Sites (XSS) é um tipo de ataque em que um site malicioso pode injetar código no seu site. A maioria dessas brechas vem de valores de formulários que seus usuários irão preencher. Você nunca deve confiar na saída dos seus usuários! Sempre assuma que todos são os melhores hackers do mundo. Eles podem injetar JavaScript ou HTML maliciosos na sua página. Esse código pode ser usado para roubar informações dos seus usuários ou executar ações no seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para evitar ataques XSS.

```php
// Vamos assumir que o usuário é esperto e tenta usar isso como seu nome
$name = '<script>alert("XSS")</script>';

// Isso irá escapar a saída
Flight::view()->set('name', $name);
// Isso irá saída: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também irá escapar automaticamente isso.
Flight::view()->render('template', ['name' => $name]);
```

## Injeção de SQL

Injeção de SQL é um tipo de ataque em que um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações do seu banco de dados ou executar ações no seu banco de dados. Novamente, você nunca deve confiar na entrada dos seus usuários! Sempre assuma que eles estão atrás de você. Você pode usar declarações preparadas nos seus objetos `PDO` para prevenir injeções de SQL.

```php
// Assumindo que você tem Flight::db() registrado como seu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser facilmente feito em uma linha
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Apenas prometa que nunca, JAMAIS fará algo assim...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque e se $username = "' OR 1=1; -- "; 
// Depois que a consulta é construída fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção de SQL muito comum que irá retornar todos os usuários.
```

## CORS

O Compartilhamento de Recursos entre Origens (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página web sejam solicitados de outro domínio fora do domínio de onde o recurso se originou. O Flight não tem funcionalidade embutida, mas isso pode ser facilmente tratado com middleware de filtros de eventos semelhante ao CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// personalize seus hosts permitidos aqui.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php ou onde você tem suas rotas
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusão

A segurança é crucial e é importante garantir que suas aplicações web sejam seguras. O Flight oferece várias funcionalidades para ajudar a proteger suas aplicações web, mas é importante sempre estar vigilante e garantir que você esteja fazendo tudo o que puder para manter os dados de seus usuários seguros. Sempre assuma o pior e nunca confie na entrada dos seus usuários. Sempre escape a saída e use declarações preparadas para prevenir injeções de SQL. Sempre use middleware para proteger suas rotas de ataques CSRF e CORS. Se você fizer todas essas coisas, estará bem encaminhado para construir aplicações web seguras.