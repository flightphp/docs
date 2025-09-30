# Segurança

## Visão Geral

A segurança é uma grande preocupação quando se trata de aplicações web. Você quer garantir que sua aplicação esteja segura e que os dados dos seus usuários estejam 
seguros. Flight fornece uma série de recursos para ajudá-lo a proteger suas aplicações web.

## Entendendo

Existem várias ameaças de segurança comuns das quais você deve estar ciente ao construir aplicações web. Algumas das ameaças mais comuns
incluem:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) ajudam com XSS escapando a saída por padrão, para que você não precise se lembrar de fazer isso. [Sessions](/awesome-plugins/session) podem ajudar com CSRF armazenando um token CSRF na sessão do usuário, conforme descrito abaixo. Usar declarações preparadas com PDO pode ajudar a prevenir ataques de injeção SQL (ou usando métodos úteis na classe [PdoWrapper](/learn/pdo-wrapper)). CORS pode ser tratado com um gancho simples antes de `Flight::start()` ser chamado.

Todos esses métodos trabalham juntos para ajudar a manter suas aplicações web seguras. Deve sempre estar na vanguarda da sua mente aprender e entender as melhores práticas de segurança.

## Uso Básico

### Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para prevenir clickjacking, XSS e outros ataques. 
Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança dos seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e 
[observatory.mozilla.org](https://observatory.mozilla.org/). Após configurar o código abaixo, você pode facilmente verificar se seus cabeçalhos estão funcionando com esses dois sites.

#### Adicionar Manualmente

Você pode adicionar esses cabeçalhos manualmente usando o método `header` no objeto `Flight\Response`.
```php
// Define o cabeçalho X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Define o cabeçalho Content-Security-Policy para prevenir XSS
// Nota: este cabeçalho pode ficar muito complexo, então você vai querer
//  consultar exemplos na internet para sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Define o cabeçalho X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Define o cabeçalho X-Content-Type-Options para prevenir sniffing de MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Define o cabeçalho Referrer-Policy para controlar quanto informação de referenciador é enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Define o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Define o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Esses podem ser adicionados no topo dos seus arquivos `routes.php` ou `index.php`.

#### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/gancho como o seguinte: 

```php
// Adiciona os cabeçalhos em um filtro
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

#### Adicionar como um Middleware

Você também pode adicioná-los como uma classe de middleware, o que fornece a maior flexibilidade para quais rotas aplicar isso. Em geral, esses cabeçalhos devem ser aplicados a todas as respostas HTML e API.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php ou onde você tem suas rotas
// FYI, este grupo de string vazia atua como um middleware global para
// todas as rotas. Claro que você poderia fazer a mesma coisa e adicionar
// isso apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) é um tipo de ataque onde um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para o seu site. 
Isso pode ser usado para realizar ações no seu site sem o conhecimento do usuário. Flight não fornece um mecanismo de proteção CSRF integrado, 
mas você pode facilmente implementar o seu próprio usando middleware.

#### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando 
o formulário for enviado. Vamos usar o plugin [flightphp/session](/awesome-plugins/session) para gerenciar sessões.

```php
// Gera um token CSRF e armazena na sessão do usuário
// (assumindo que você criou um objeto de sessão e o anexou ao Flight)
// veja a documentação de sessão para mais informações
Flight::register('session', flight\Session::class);

// Você só precisa gerar um único token por sessão (para que funcione 
// em várias abas e solicitações para o mesmo usuário)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando o Template Padrão PHP Flight

```html
<!-- Use o token CSRF no seu formulário -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- outros campos do formulário -->
</form>
```

##### Usando Latte

Você também pode definir uma função personalizada para exibir o token CSRF em seus templates Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// outras configurações...

	// Define uma função personalizada para exibir o token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

E agora em seus templates Latte, você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

#### Verificar o Token CSRF

Você pode verificar o token CSRF usando vários métodos.

##### Middleware

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php ou onde você tem suas rotas
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mais rotas
}, [ CsrfMiddleware::class ]);
```

##### Filtros de Evento

```php
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura o token csrf dos valores do formulário
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// ou para uma resposta JSON
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) é um tipo de ataque onde uma entrada de formulário maliciosa pode injetar código no seu site. A maioria dessas oportunidades vem 
de valores de formulário que seus usuários finais preencherão. Você **nunca** deve confiar na saída dos seus usuários! Sempre assuma que todos eles são os 
melhores hackers do mundo. Eles podem injetar JavaScript ou HTML malicioso na sua página. Esse código pode ser usado para roubar informações dos seus 
usuários ou realizar ações no seu site. Usando a classe de visualização do Flight ou outro motor de templating como [Latte](/awesome-plugins/latte), você pode facilmente escapar a saída para prevenir ataques XSS.

```php
// Vamos assumir que o usuário é esperto e tenta usar isso como seu nome
$name = '<script>alert("XSS")</script>';

// Isso escapará a saída
Flight::view()->set('name', $name);
// Isso exibirá: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também escapará isso automaticamente.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection é um tipo de ataque onde um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações 
do seu banco de dados ou realizar ações no seu banco de dados. Novamente, você **nunca** deve confiar na entrada dos seus usuários! Sempre assuma que eles estão 
em busca de sangue. Você pode usar declarações preparadas em seus objetos `PDO` para prevenir injeção SQL.

```php
// Assumindo que você tem Flight::db() registrado como seu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser feito facilmente em uma linha
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Você pode fazer a mesma coisa com um objeto PDO com placeholders ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Exemplo Inseguro

O abaixo é o motivo pelo qual usamos declarações preparadas SQL para proteger de exemplos inocentes como o abaixo:

```php
// o usuário final preenche um formulário web.
// para o valor do formulário, o hacker coloca algo como isso:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Após a consulta ser construída, fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção SQL muito comum que retornará todos os usuários.

var_dump($users); // isso despejará todos os usuários no banco de dados, não apenas o único nome de usuário
```

### CORS

Cross-Origin Resource Sharing (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página web sejam 
solicitados de outro domínio fora do domínio de origem do recurso. Flight não tem funcionalidade integrada, 
mas isso pode ser facilmente tratado com um gancho para executar antes do método `Flight::start()` ser chamado.

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

// index.php ou onde você tem suas rotas
$CorsUtil = new CorsUtil();

// Isso precisa ser executado antes de start ser executado.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Tratamento de Erros
Esconda detalhes de erros sensíveis em produção para evitar vazamento de informações para atacantes. Em produção, registre erros em vez de exibi-los com `display_errors` definido como `0`.

```php
// No seu bootstrap.php ou index.php

// adicione isso ao seu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Desabilita exibição de erros
    ini_set('log_errors', 1);     // Registra erros em vez disso
    ini_set('error_log', '/path/to/error.log');
}

// Nas suas rotas ou controladores
// Use Flight::halt() para respostas de erro controladas
Flight::halt(403, 'Access denied');
```

### Sanitização de Entrada
Nunca confie na entrada do usuário. Sanitize-a usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes de processar para prevenir que dados maliciosos se infiltrem.

```php

// Vamos assumir uma solicitação $_POST com $_POST['input'] e $_POST['email']

// Sanitiza uma entrada de string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitiza um email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hash de Senhas
Armazene senhas de forma segura e verifique-as com segurança usando as funções integradas do PHP como [password_hash](https://www.php.net/manual/en/function.password-hash.php) e [password_verify](https://www.php.net/manual/en/function.password-verify.php). As senhas nunca devem ser armazenadas em texto plano, nem devem ser criptografadas com métodos reversíveis. O hashing garante que, mesmo se o seu banco de dados for comprometido, as senhas reais permaneçam protegidas.

```php
$password = Flight::request()->data->password;
// Hash de uma senha ao armazenar (por exemplo, durante o registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica uma senha (por exemplo, durante o login)
if (password_verify($password, $stored_hash)) {
    // Senha corresponde
}
```

### Limitação de Taxa
Proteja contra ataques de força bruta ou ataques de negação de serviço limitando as taxas de solicitação com um cache.

```php
// Assumindo que você tem flightphp/cache instalado e registrado
// Usando flightphp/cache em um filtro
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Redefine após 60 segundos
});
```

## Veja Também
- [Sessions](/awesome-plugins/session) - Como gerenciar sessões de usuário de forma segura.
- [Templates](/learn/templates) - Usando templates para escapar saída automaticamente e prevenir XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Interações simplificadas com banco de dados usando declarações preparadas.
- [Middleware](/learn/middleware) - Como usar middleware para simplificar o processo de adicionar cabeçalhos de segurança.
- [Responses](/learn/responses) - Como personalizar respostas HTTP com cabeçalhos seguros.
- [Requests](/learn/requests) - Como lidar e sanitizar entrada do usuário.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Função PHP para sanitização de entrada.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Função PHP para hashing seguro de senhas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Função PHP para verificar senhas hasheadas.

## Solução de Problemas
- Consulte a seção "Veja Também" acima para informações de solução de problemas relacionadas a problemas com componentes do Flight Framework.

## Changelog
- v3.1.0 - Adicionadas seções sobre CORS, Tratamento de Erros, Sanitização de Entrada, Hash de Senhas e Limitação de Taxa.
- v2.0 - Adicionado escaping para visualizações padrão para prevenir XSS.