# Segurança

A segurança é fundamental quando se trata de aplicações da web. Você quer garantir que sua aplicação seja segura e que os dados de seus usuários estejam protegidos. O Flight fornece várias funcionalidades para ajudar a garantir a segurança de suas aplicações web.

## Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para evitar clickjacking, XSS e outros ataques. Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

### Adicionar Manualmente

Você pode adicionar manualmente esses cabeçalhos usando o método `header` no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para evitar clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para evitar XSS
// Observação: este cabeçalho pode se tornar muito complexo, então você vai querer
// consultar exemplos na internet para sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para evitar XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para evitar a detecção de tipo MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar a quantidade de informações do referrer enviadas
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Esses podem ser adicionados no topo de seus arquivos `bootstrap.php` ou `index.php`.

### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/hook como abaixo: 

```php
// Adicione os cabeçalhos em um filtro
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
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
	}
}

// index.php ou onde você tem suas rotas
// FYI, esse grupo de string vazia atua como um middleware global para
// todas as rotas. Claro que você pode fazer a mesma coisa e adicionar
// apenas a rotas específicas.
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'ControladorDeUsuario', 'pegarUsuarios' ]);
	// mais rotas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificação de Solicitação entre Sites (CSRF)

A falsificação de solicitação entre sites (CSRF) é um tipo de ataque em que um site malicioso pode fazer o navegador de um usuário enviar uma solicitação para seu site. Isso pode ser usado para realizar ações em seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF integrado, mas você pode implementar facilmente o seu próprio usando middleware.

### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando o formulário for enviado.

```php
// Gere um token CSRF e armazene-o na sessão do usuário
// (pressupondo que você criou um objeto de sessão e o anexou ao Flight)
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
// Defina uma função personalizada para exibir o token CSRF
// Observação: a View foi configurada com Latte como o motor de visualização
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

E agora em seus modelos Latte você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

Rápido e simples, não é?

### Verifique o Token CSRF

Você pode verificar o token CSRF usando filtros de eventos:

```php
// Este middleware verifica se a solicitação é uma solicitação POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura o token csrf nos valores do formulário
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
	$router->get('/usuarios', [ 'ControladorDeUsuario', 'pegarUsuarios' ]);
	// mais rotas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

A falsificação de solicitação entre sites (XSS) é um tipo de ataque em que um site malicioso pode injetar código em seu site. A maioria destas oportunidades vem de valores de formulários que seus usuários preencherão. Você nunca deve confiar na saída de seus usuários! Sempre assuma que todos são os melhores hackers do mundo. Eles podem inserir JavaScript ou HTML maliciosos em sua página. Este código pode ser usado para roubar informações de seus usuários ou realizar ações em seu site. Usando a classe de visualização do Flight, você pode escapar facilmente a saída para evitar ataques de XSS.

```php
// Vamos supor que o usuário seja esperto e tente usar isso como seu nome
$nome = '<script>alert("XSS")</script>';

// Isso escapará a saída
Flight::view()->set('nome', $nome);
// Isso vai produzir: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Se você usar algo como Latte registrado como sua classe de visualização, ele também irá escapar automaticamente isso.
Flight::view()->render('modelo', ['nome' => $nome]);
```

## Injeção de SQL

A injeção de SQL é um tipo de ataque em que um usuário mal-intencionado pode injetar código SQL em seu banco de dados. Isso pode ser usado para roubar informações de seu banco de dados ou realizar ações em seu banco de dados. Novamente, você nunca deve confiar na entrada de seus usuários! Sempre assuma que eles estão atrás de você. Você pode usar declarações preparadas em seus objetos `PDO` para prevenir injeções de SQL.

```php
// Supondo que você tenha o Flight::db() registrado como seu objeto PDO
$declaracao = Flight::db()->prepare('SELECT * FROM usuarios WHERE username = :username');
$declaracao->execute([':username' => $nomeDeUsuario]);
$usuarios = $declaracao->fetchAll();

// Se você usar a classe PdoWrapper, isso pode ser facilmente feito em uma linha
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE username = :username', [ 'username' => $nomeDeUsuario ]);

// Você pode fazer a mesma coisa com um objeto PDO com espaços reservados de ?
$declaracao = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE username = ?', [ $nomeDeUsuario ]);

// Apenas prometa que nunca FARÁ algo assim...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE username = '{$nomeDeUsuario}' LIMIT 5");
// porque e se $nomeDeUsuario = "' OR 1=1; -- "; 
// Depois que a consulta é construída, ela parece com isso
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// É um ataque comum de injeção de SQL que retorna todos os usuários.
```

## CORS

O Compartilhamento de Recursos de Origem Cruzada (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript, etc.) em uma página web sejam solicitados de outro domínio fora do domínio de onde o recurso se originou. O Flight não possui funcionalidade embutida, mas isso pode ser facilmente tratado com middleware ou filtros de eventos semelhantes ao CSRF.

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
		// customize seus hosts permitidos aqui.
		$permitidos = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $permitidos)) {
			$resposta = Flight::response();
			$resposta->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php ou onde você tem suas rotas
Flight::route('/usuarios', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios');
	Flight::json($usuarios);
})->addMiddleware(new CorsMiddleware());
```

## Conclusão

A segurança é fundamental e é importante garantir que suas aplicações web sejam seguras. O Flight fornece várias funcionalidades para ajudar a garantir a segurança de suas aplicações web, mas é importante sempre estar vigilante e garantir que você está fazendo tudo o que pode para manter os dados de seus usuários seguros. Sempre assuma o pior e nunca confie na entrada de seus usuários. Sempre escape a saída e use declarações preparadas para prevenir injeções de SQL. Sempre use middleware para proteger suas rotas de ataques CSRF e CORS. Se você fizer todas essas coisas, estará no caminho certo para construir aplicações web seguras.