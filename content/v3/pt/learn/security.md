# Segurança

## Visão geral

Segurança é um assunto sério quando se trata de aplicações web. Você quer garantir que sua aplicação seja segura e que os dados dos seus usuários estejam
protegidos. O Flight fornece vários recursos para ajudar você a proteger suas aplicações web.

O [skeleton](https://github.com/flightphp/skeleton) oficial também inclui um **`SECURITY.md`** dedicado e um middleware de cabeçalhos de segurança para que [ferramentas de codificação com IA](/learn/ai) (e humanos) tenham um local deliberado para segredos, cabeçalhos e regras de XSS/SQL — separado do estilo geral de codificação em `AGENTS.md`.

## Compreensão

Existem várias ameaças comuns de segurança das quais você deve estar ciente ao criar aplicações web. Algumas das ameaças mais comuns
incluem:
- Falsificação de Solicitação entre Sites (CSRF)
- Cross Site Scripting (XSS)
- Injeção de SQL
- Compartilhamento de Recursos entre Origens (CORS)

Os [templates](/learn/templates) ajudam com XSS ao escapar a saída por padrão (Twig e Latte fazem isso; aproveite essa vantagem). As [sessões](/awesome-plugins/session) podem ajudar com CSRF ao armazenar um token CSRF na sessão do usuário, conforme descrito abaixo. Usar instruções preparadas com PDO — ou helpers no [SimplePdo](/learn/simple-pdo) — ajuda a prevenir injeção de SQL. O CORS pode ser tratado com um hook simples antes da chamada de `Flight::start()`.

Todos esses métodos trabalham juntos para ajudar a manter suas aplicações web seguras. Isso deve estar sempre em primeiro plano na sua mente para aprender e entender as melhores práticas de segurança. Não peça a um assistente de IA para "desativar CSP" ou enfraquecer cabeçalhos apenas para fazer uma página carregar sem entender o trade-off.

## Uso básico

### Cabeçalhos

Cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para prevenir clickjacking, XSS e outros ataques.
Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança dos seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e
[observatory.mozilla.org](https://observatory.mozilla.org/). Depois de configurar o código abaixo, você pode verificar facilmente se seus cabeçalhos estão funcionando com esses dois sites.

O skeleton inclui `App\Middleware\SecurityHeadersMiddleware` (CSP com um nonce por requisição, opções de frame, HSTS e mais). Prefira estendê-lo deliberadamente em vez de desativar cabeçalhos.

#### Adicionar manualmente

Você pode adicionar esses cabeçalhos manualmente usando o método `header` no objeto `Flight\Response`.
```php
// Defina o cabeçalho X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Defina o cabeçalho Content-Security-Policy para prevenir XSS
// Nota: este cabeçalho pode ficar muito complexo, então você vai querer
//  consultar exemplos na internet para sua aplicação
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Defina o cabeçalho X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Defina o cabeçalho X-Content-Type-Options para prevenir sniffing de MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Defina o cabeçalho Referrer-Policy para controlar quanta informação de referrer é enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Defina o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Defina o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Eles podem ser adicionados no topo dos seus arquivos `routes.php` ou `index.php`.

#### Adicionar como filtro

Você também pode adicioná-los em um filtro/hook como o seguinte:

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

#### Adicionar como middleware

Você também pode adicioná-los como uma classe de middleware, que oferece a maior flexibilidade sobre a quais rotas aplicá-los. Em geral, esses cabeçalhos devem ser aplicados a todas as respostas HTML e de API.

Caminho e namespace no estilo do skeleton (**o uso de maiúsculas/minúsculas da pasta corresponde a `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Prefira um nonce de CSP do bootstrap quando você tiver scripts inline (o skeleton define csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — grupo de string vazia = middleware global para todas as rotas
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// mais rotas
}, [SecurityHeadersMiddleware::class]);
```

Projetos mais antigos ainda podem usar `app/middlewares` e `app\middlewares`; isso funciona se as pastas corresponderem. Novos apps do skeleton usam **`app/Middleware/`** e **`App\Middleware`**. Veja [Carregamento automático](/learn/autoloading).

### Falsificação de Solicitação entre Sites (CSRF)

Falsificação de Solicitação entre Sites (CSRF) é um tipo de ataque no qual um site malicioso pode fazer o navegador de um usuário enviar uma requisição para o seu site.
Isso pode ser usado para executar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo integrado de proteção
CSRF, mas você pode facilmente implementar o seu próprio usando middleware.

#### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Depois, você pode usar esse token nos seus formulários e verificá-lo quando
o formulário for enviado. Usaremos o plugin [flightphp/session](/awesome-plugins/session) para gerenciar sessões.

```php
// Gere um token CSRF e armazene-o na sessão do usuário
// (assumindo que você criou um objeto de sessão e o anexou ao Flight)
// veja a documentação de sessão para mais informações
Flight::register('session', flight\Session::class);

// Você só precisa gerar um único token por sessão (assim ele funciona
// em várias abas e requisições para o mesmo usuário)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando o template PHP padrão do Flight

```html
<!-- Use o token CSRF no seu formulário -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- outros campos do formulário -->
</form>
```

##### Usando Twig (padrão do skeleton)

Registre uma função Twig ou passe o token para cada view de formulário. Exemplo mínimo com um global + campo de formulário:

```php
// Ao configurar o Twig (ex.: services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# outros campos #}
</form>
```

##### Usando Latte

Você também pode definir uma função personalizada para exibir o token CSRF nos seus templates Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// outras configurações...

	// Defina uma função personalizada para exibir o token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

E agora, nos seus templates Latte, você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

#### Verificar o token CSRF

Você pode verificar o token CSRF usando vários métodos.

##### Middleware

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// mais rotas
}, [CsrfMiddleware::class]);
```

##### Filtros de evento

```php
// Este middleware verifica se a requisição é uma requisição POST e, se for, verifica se o token CSRF é válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture o token csrf dos valores do formulário
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

Cross Site Scripting (XSS) é um tipo de ataque no qual uma entrada maliciosa de formulário pode injetar código no seu site. A maioria dessas oportunidades vem
de valores de formulário que seus usuários finais preencherão. Você **nunca** deve confiar na saída dos seus usuários! Sempre assuma que todos eles são os
melhores hackers do mundo. Eles podem injetar JavaScript ou HTML malicioso na sua página. Esse código pode ser usado para roubar informações dos seus
usuários ou executar ações no seu site. Usando a classe de view do Flight ou um mecanismo de template como [Twig](/awesome-plugins/twig) ou [Latte](/awesome-plugins/latte), você pode escapar facilmente a saída para prevenir ataques XSS.

```php
// Vamos supor que o usuário é esperto e tenta usar isto como nome
$name = '<script>alert("XSS")</script>';

// Isto escapará a saída
Flight::view()->set('name', $name);
// Isto exibirá: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (padrão do skeleton) e Latte escapam automaticamente por padrão — prefira-os em vez de echo PHP puro
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escapado
// Evite |raw / saída não escapada a menos que o conteúdo seja totalmente confiável
```

### Injeção de SQL

Injeção de SQL é um tipo de ataque no qual um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações
do seu banco de dados ou executar ações no seu banco de dados. Novamente, você **nunca** deve confiar na entrada dos seus usuários! Sempre assuma que eles
estão em busca de sangue. Use instruções preparadas — os helpers do [SimplePdo](/learn/simple-pdo) tornam esse o caminho padrão.

```php
// Assumindo que você registrou Flight::db() como SimplePdo (ou injete SimplePdo no controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (preferido) — one-liners com parâmetros vinculados
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Mesma ideia com placeholders ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Em controllers no estilo do skeleton, prefira injeção via construtor de `SimplePdo` em vez de `Flight::db()` para que testes e código gerado por IA permaneçam consistentes ([DIC](/learn/dependency-injection-container)).

#### Exemplo inseguro

O abaixo é o motivo pelo qual usamos instruções SQL preparadas para nos proteger de exemplos inocentes como o seguinte:

```php
// o usuário final preenche um formulário web.
// para o valor do formulário, o hacker insere algo como isto:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Depois que a consulta é construída, ela fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Parece estranho, mas é uma consulta válida que funcionará. Na verdade,
// é um ataque de injeção de SQL muito comum que retornará todos os usuários.

var_dump($users); // isto despejará todos os usuários do banco de dados, não apenas o único nome de usuário
```

### Segredos e configuração

- Coloque segredos no **`.env`** (ou no ambiente real), não em exemplos de `config.php` commitados.
- Regra do skeleton: padrões literais em `config.php`; mescle o env no bootstrap; **não** leia `$_ENV` dentro de controllers — injete configuração em vez disso. Veja [Configuração](/learn/configuration).
- Nunca faça commit de chaves de API, senhas de banco de dados ou chaves de criptografia de sessão. Aponte ferramentas de IA para **`SECURITY.md`** para que elas não inventem atalhos inseguros.

### Validação de callback JSONP

Se você usa o método `Flight::jsonp()`, esteja ciente de que o Flight valida o nome do parâmetro de callback JSONP contra uma regex de allowlist estrita (`/^[A-Za-z_$][\w$.]{0,127}$/`). Qualquer nome de callback que não corresponda a esse padrão fará o Flight lançar uma exceção, impedindo a injeção de JavaScript arbitrário por meio de um valor de callback malicioso.

Essa validação é integrada e não requer configuração adicional, mas vale a pena conhecê-la ao depurar erros inesperados de endpoints JSONP.

### CORS

Compartilhamento de Recursos entre Origens (CORS) é um mecanismo que permite que muitos recursos (por exemplo, fontes, JavaScript etc.) em uma página web sejam
solicitados de outro domínio fora do domínio do qual o recurso se originou. O Flight não tem funcionalidade integrada,
mas isso pode ser facilmente tratado com um hook para executar antes da chamada do método `Flight::start()`.

```php
// app/Utils/CorsUtil.php  (skeleton: pasta Utils em PascalCase → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / rotas — execute antes de start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Fortalecimento da configuração do Flight

O Flight expõe várias configurações de engine que têm implicações diretas de segurança. Defini-las corretamente é uma das maneiras mais fáceis de fortalecer sua aplicação.

#### `flight.allow_method_override`

Por padrão, o Flight permite que clientes substituam o método HTTP de uma requisição usando o cabeçalho `X-HTTP-Method-Override` ou um campo `_method` em um corpo POST. Embora isso seja útil para formulários HTML que só conseguem enviar `GET`/`POST`, pode ser perigoso se você não espera isso — um atacante poderia forjar requisições `DELETE` ou `PUT` por meio de um formulário comum.

Se sua aplicação não depende desse comportamento (por exemplo, você está criando uma API consumida por clientes modernos ou frontends JavaScript que podem enviar qualquer verbo HTTP), você deve desativá-lo:

```php
// No seu index.php ou arquivo de bootstrap, antes de Flight::start()
Flight::set('flight.allow_method_override', false);
```

O valor padrão é `true` por compatibilidade retroativa, mas **defini-lo como `false` é fortemente recomendado** para qualquer aplicação que não precise explicitamente do recurso de substituição.

#### `flight.debug`

O Flight tem uma configuração `flight.debug` que controla se informações detalhadas de erro (mensagem da exceção, código e rastreamento completo da pilha) são renderizadas no navegador quando ocorre uma exceção não tratada. O padrão é `false`, o que significa que apenas uma mensagem genérica `500 Internal Server Error` é exibida — nenhum detalhe interno é vazado para o cliente.

Nunca habilite isso em um servidor de produção. Use apenas localmente ou em um ambiente de staging:

```php
// Seguro apenas para desenvolvimento local — NUNCA em produção
Flight::set('flight.debug', true);
```

Quando `flight.debug` é `false` (o padrão), você ainda pode capturar erros habilitando `flight.log_errors`:

```php
// Registre erros no servidor sem expô-los ao cliente
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

A classe `View` integrada do Flight incluirá tranquilamente um template a partir de um caminho absoluto, ou de um nome relativo que sai de `flight.views.path` (por exemplo, com `../`). Isso é intencional para apps que compartilham deliberadamente templates entre pastas, mas também é um risco de path traversal se um nome de template algum dia vier de entrada não confiável.

`flight.views.restrict_to_path` fica **desativado por padrão** para que apps existentes continuem funcionando. Ative-o a menos que você tenha um motivo documentado para não fazer isso:

```php
// No seu index.php ou arquivo de bootstrap, antes de Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

O Engine aplica essa configuração em `View::$restrictToPath` quando a view é criada (mesmo padrão de `flight.views.path` e `flight.views.extension`). Com ela ativada:

- `render()` e `fetch()` incluem apenas arquivos cujo caminho real fica dentro do diretório de views configurado (symlinks que apontam para fora também são rejeitados).
- `exists()` retorna `false` para esses mesmos caminhos em vez de lançar exceção.
- `getTemplate()` em si não muda — ele continua retornando caminhos da mesma forma que sempre fez.
- Um arquivo bloqueado lança `Template file is outside the views path.` Um arquivo ausente ainda lança a mensagem existente `Template file not found: ...`.

Se você usa Twig ou Latte com seus próprios loaders de filesystem apontados para o diretório de views, esses engines já restringem templates a essa raiz. Ainda assim, ative isto para a `View` nativa do Flight para que qualquer código que chame `Flight::view()->render()` / `fetch()` obtenha a mesma proteção. O [skeleton](https://github.com/flightphp/skeleton) oficial o habilita no bootstrap.

#### Configuração de produção recomendada

```php
// index.php ou aplicado a partir da configuração do app / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Tratamento de erros
Oculte detalhes sensíveis de erros em produção para evitar vazar informações para atacantes. Em produção, registre erros em vez de exibi-los com `display_errors` definido como `0`.

```php
// No seu bootstrap.php ou index.php

// adicione isto ao seu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Desativa a exibição de erros
    ini_set('log_errors', 1);     // Registra erros em vez disso
    ini_set('error_log', '/path/to/error.log');
}

// Nas suas rotas ou controllers
// Use Flight::halt() para respostas de erro controladas
Flight::halt(403, 'Access denied');
```

### Sanitização de entrada
Nunca confie na entrada do usuário. Sanitize-a usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes do processamento para impedir que dados maliciosos entrem despercebidos. Prefira ler a entrada via `$app->request()` (ou `Flight::request()`) em vez de `$_GET` / `$_POST` brutos no código do app.

```php

// Vamos supor uma requisição $_POST com $_POST['input'] e $_POST['email']

// Sanitize uma entrada de string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitize um e-mail
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hash de senhas
Armazene senhas com segurança e verifique-as com segurança usando as funções integradas do PHP, como [password_hash](https://www.php.net/manual/en/function.password-hash.php) e [password_verify](https://www.php.net/manual/en/function.password-verify.php). Senhas nunca devem ser armazenadas em texto puro, nem devem ser criptografadas com métodos reversíveis. O hash garante que, mesmo se seu banco de dados for comprometido, as senhas reais permaneçam protegidas.

```php
$password = Flight::request()->data->password;
// Gere o hash de uma senha ao armazenar (ex.: durante o registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifique uma senha (ex.: durante o login)
if (password_verify($password, $stored_hash)) {
    // A senha corresponde
}
```

### Limitação de taxa
Proteja contra ataques de força bruta ou ataques de negação de serviço limitando as taxas de requisição com um cache.

```php
// Assumindo que você instalou e registrou flightphp/cache
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

## Veja também
- [Sessões](/awesome-plugins/session) - Como gerenciar sessões de usuário com segurança.
- [Templates](/learn/templates) - Escape automático do Twig/Latte e XSS.
- [SimplePdo](/learn/simple-pdo) - Helpers de banco de dados com instruções preparadas.
- [PdoWrapper](/learn/pdo-wrapper) - Obsoleto; use SimplePdo para código novo.
- [Middleware](/learn/middleware) - Como usar middleware para simplificar o processo de adicionar cabeçalhos de segurança.
- [Configuração](/learn/configuration) - `.env` vs configuração literal, flags de produção.
- [IA e experiência do desenvolvedor](/learn/ai) - Mantenha a política de segurança em `SECURITY.md` para agentes.
- [Respostas](/learn/responses) - Como personalizar respostas HTTP com cabeçalhos seguros.
- [Requisições](/learn/requests) - Como lidar e sanitizar a entrada do usuário.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Função PHP para sanitização de entrada.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Função PHP para hash seguro de senhas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Função PHP para verificar senhas com hash.

## Solução de problemas
- Consulte a seção "Veja também" acima para obter informações de solução de problemas relacionadas a componentes do Flight Framework.
- Se a CSP bloquear seus scripts, adicione um nonce (padrão do skeleton) ou permita origens específicas — não defina `script-src *` sem um plano.

## Changelog
- Docs – `App\Middleware` do skeleton, notas de CSRF/XSS no Twig, SimplePdo, segredos/`.env` e `SECURITY.md` para projetos amigáveis a IA.
- Docs – Documentado `flight.views.restrict_to_path` em Fortalecimento da configuração do Flight (contenção de caminho opcional para views nativas).
- v3.18.1 - Adicionada seção de Fortalecimento da configuração do Flight cobrindo `flight.allow_method_override`, `flight.debug` e validação de callback JSONP.
- v3.1.0 - Adicionadas seções sobre CORS, tratamento de erros, sanitização de entrada, hash de senhas e limitação de taxa.
- v2.0 - Adicionado escape para views padrão para prevenir XSS.