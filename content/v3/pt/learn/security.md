# Segurança

A segurança é um assunto importante quando se trata de aplicativos web. Você quer ter certeza de que seu aplicativo é seguro e que os dados dos seus usuários estão 
salvos. Flight oferece uma série de recursos para ajudá-lo a proteger seus aplicativos web.

## Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger seus aplicativos web. Você pode usar cabeçalhos para prevenir clickjacking, XSS e outros ataques. 
Existem várias maneiras de adicionar esses cabeçalhos ao seu aplicativo.

Dois ótimos sites para verificar a segurança dos seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Adicionar Manualmente

Você pode adicionar esses cabeçalhos manualmente usando o método `header` no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para prevenir XSS
// Nota: este cabeçalho pode ficar muito complexo, então você vai querer
// consultar exemplos na internet para o seu aplicativo
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para prevenir sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar quanta informação de referenciador é enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Defina o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Esses podem ser adicionados no topo dos seus arquivos `bootstrap.php` ou `index.php`.

### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/gatilho como o seguinte: 

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

// index.php ou onde quer que você tenha suas rotas
// FYI, este grupo de string vazia atua como um middleware global para
// todas as rotas. Claro que você poderia fazer a mesma coisa e adicionar
// isso apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

O Cross Site Request Forgery (CSRF) é um tipo de ataque onde um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para seu site. 
Isso pode ser usado para realizar ações em seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF embutido, 
mas você pode facilmente implementar o seu usando middleware.

### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando 
o formulário for enviado.

```php
// Gere um token CSRF e armazene-o na sessão do usuário
// (supondo que você tenha criado um objeto de sessão e o anexado ao Flight)
// consulte a documentação da sessão para mais informações
Flight::register('session', \Ghostff\Session\Session::class);

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

Você também pode definir uma função personalizada para exibir o token CSRF em seus templates Latte.

```php
// Defina uma função personalizada para exibir o token CSRF
// Nota: A View foi configurada com Latte como o mecanismo de visualização
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

E agora, em seus templates Latte você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

Curto e simples, certo?

### Verificar o Token CSRF

Você pode verificar o token CSRF usando filtros de evento:

```php
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture o token csrf dos valores do formulário
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
			// ou para uma resposta JSON
			Flight::jsonHalt(['error' => 'Token CSRF inválido'], 403);
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

// index.php ou onde quer que você tenha suas rotas
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

O Cross Site Scripting (XSS) é um tipo de ataque onde um site malicioso pode injetar código em seu site. A maioria dessas oportunidades vem 
de valores de formulário que seus usuários finais preencherão. Você **nunca** deve confiar na saída de seus usuários! Sempre assuma que todos eles são os 
melhores hackers do mundo. Eles podem injetar JavaScript ou HTML malicioso em sua página. Este código pode ser usado para roubar informações de seus 
usuários ou realizar ações em seu site. Usando a classe de visualização do Flight, você pode facilmente escapar da saída para prevenir ataques XSS.

```php
// Vamos supor que o usuário é esperto e tenta usar isto como seu nome
$name = '<script>alert("XSS")</script>';

// Isso vai escapar a saída
Flight::view()->set('name', $name);
// Isso vai exibir: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, isso também escapará automaticamente.
Flight::view()->render('template', ['name' => $name]);
```

## Injeção de SQL

A injeção de SQL é um tipo de ataque onde um usuário malicioso pode injetar código SQL em seu banco de dados. Isso pode ser usado para roubar informações 
de seu banco de dados ou realizar ações em seu banco de dados. Novamente, você deve **nunca** confiar na entrada de seus usuários! Sempre assuma que eles estão 
em busca de sangue. Você pode usar instruções preparadas em seus objetos `PDO` para prevenir a injeção de SQL.

```php
// Supondo que você tenha Flight::db() registrado como seu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser facilmente feito em uma linha
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Você pode fazer a mesma coisa com um objeto PDO com ? espaços reservados
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Apenas prometa que você nunca NUNCA fará algo como isso...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque e se $username = "' OR 1=1; -- "; 
// Depois que a consulta é construída, ela fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque muito comum de injeção de SQL que retornará todos os usuários.
```

## CORS

O Cross-Origin Resource Sharing (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página web sejam 
solicitados de outro domínio fora do domínio de onde o recurso se originou. O Flight não tem funcionalidade embutida, 
mas isso pode ser facilmente tratado com um gancho para ser executado antes que o método `Flight::start()` seja chamado.

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
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php ou onde quer que você tenha suas rotas
$CorsUtil = new CorsUtil();

// Isso precisa ser executado antes que o start seja executado.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Tratamento de Erros
Oculte detalhes de erro sensíveis na produção para evitar vazamento de informações para atacantes.

```php
// No seu bootstrap.php ou index.php

// no flightphp/skeleton, isso está em app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Desabilitar a exibição de erros
    ini_set('log_errors', 1);     // Registrar erros em vez disso
    ini_set('error_log', '/path/to/error.log');
}

// Nas suas rotas ou controladores
// Use Flight::halt() para respostas de erro controladas
Flight::halt(403, 'Acesso negado');
```

## Sanitização de Entradas
Nunca confie na entrada do usuário. Sanitizá-la antes de processar para evitar que dados maliciosos sejam inseridos.

```php

// Vamos supor uma solicitação $_POST com $_POST['input'] e $_POST['email']

// Sanitizar uma entrada de string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitizar um email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Hashing de Senhas
Armazene senhas de forma segura e verifique-as com segurança usando as funções embutidas do PHP.

```php
$password = Flight::request()->data->password;
// Hash uma senha ao armazená-la (por exemplo, durante o registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifique uma senha (por exemplo, durante o login)
if (password_verify($password, $stored_hash)) {
    // A senha corresponde
}
```

## Limitação de Taxa
Proteja-se contra ataques de força bruta limitando as taxas de solicitação com um cache.

```php
// Supondo que você tenha flightphp/cache instalado e registrado
// Usando flightphp/cache em um middleware
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Muitas solicitações');
    }
    
    $cache->set($key, $attempts + 1, 60); // Redefinir após 60 segundos
});
```

## Conclusão

A segurança é um assunto importante e é fundamental garantir que seus aplicativos web sejam seguros. O Flight oferece uma série de recursos para ajudá-lo a 
proteger seus aplicativos web, mas é importante estar sempre alerta e garantir que você está fazendo tudo que pode para manter os dados dos seus usuários 
seguros. Sempre assuma o pior e nunca confie na entrada dos seus usuários. Sempre escape a saída e use instruções preparadas para evitar a injeção de SQL. 
Sempre use middleware para proteger suas rotas contra ataques CSRF e CORS. Se você fizer tudo isso, estará no caminho certo para construir aplicativos web seguros.