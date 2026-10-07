# Segurança

## Visão Geral

Segurança é um grande problema quando se trata de aplicações web. Você quer garantir que sua aplicação seja segura e que os dados dos seus usuários estejam
seguros. O Flight fornece uma série de recursos para ajudar você a proteger suas aplicações web.

O [skeleton](https://github.com/flightphp/skeleton) oficial também inclui um **`SECURITY.md`** dedicado e middleware de cabeçalhos de segurança, para que [ferramentas de codificação por IA](/learn/ai) (e humanos) tenham um lugar deliberado para segredos, cabeçalhos e regras de XSS/SQL—separado do estilo geral de codificação em `AGENTS.md`.

## Entendendo

Existem várias ameaças de segurança comuns que você deve estar ciente ao construir aplicações web. Algumas das ameaças mais comuns
incluem:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- Injeção de SQL
- Cross Origin Resource Sharing (CORS)

Os [Templates](/learn/templates) ajudam com XSS ao escapar a saída por padrão (Twig e Latte fazem isso; aproveite essa vantagem). As [Sessões](/awesome-plugins/session) podem ajudar com CSRF armazenando um token CSRF na sessão do usuário, conforme descrito abaixo. Usar declarações preparadas com PDO—ou os helpers do [SimplePdo](/learn/simple-pdo)—ajuda a prevenir injeção de SQL. O CORS pode ser tratado com um simples hook antes que `Flight::start()` seja chamado.

Todos esses métodos trabalham juntos para ajudar a manter suas aplicações web seguras. Deve estar sempre em primeiro lugar na sua mente aprender e entender as melhores práticas de segurança. Não peça a um assistente de IA para "desabilitar o CSP" ou enfraquecer cabeçalhos apenas para fazer uma página carregar sem entender a compensação.

## Uso Básico

### Cabeçalhos

Os cabeçalhos HTTP são uma das maneiras mais fáceis de proteger suas aplicações web. Você pode usar cabeçalhos para prevenir clickjacking, XSS e outros ataques.
Existem várias maneiras de adicionar esses cabeçalhos à sua aplicação.

Dois ótimos sites para verificar a segurança dos seus cabeçalhos são [securityheaders.com](https://securityheaders.com/) e
[observatory.mozilla.org](https://observatory.mozilla.org/). Depois que você configurar o código abaixo, pode verificar facilmente se seus cabeçalhos estão funcionando nesses dois sites.

O skeleton inclui `App\Middleware\SecurityHeadersMiddleware` (CSP com nonce por requisição, opções de frame, HSTS e muito mais). Prefira estender isso deliberadamente em vez de desativar os cabeçalhos.

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

// Define o cabeçalho X-Content-Type-Options para prevenir MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Define o cabeçalho Referrer-Policy para controlar quanta informação de referência é enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Define o cabeçalho Strict-Transport-Security para forçar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Define o cabeçalho Permissions-Policy para controlar quais recursos e APIs podem ser usados
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Eles podem ser adicionados no topo dos seus arquivos `routes.php` ou `index.php`.

#### Adicionar como um Filtro

Você também pode adicioná-los em um filtro/hook da seguinte maneira:

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

Você também pode adicioná-los como uma classe de middleware, o que oferece a maior flexibilidade para decidir em quais rotas aplicá-los. Em geral, esses cabeçalhos devem ser aplicados a todas as respostas HTML e de API.

Caminho e namespace no estilo do skeleton (**a pasta com maiúsculas coincide com `App\Middleware`**):

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
		// Prefira um nonce CSP do bootstrap quando você tiver scripts inline (o skeleton define csp_nonce)
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

Projetos mais antigos podem ainda usar `app/middlewares` e `app\middlewares`; isso funciona se as pastas coincidirem. Novos aplicativos skeleton usam **`app/Middleware/`** e **`App\Middleware`**. Consulte [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) é um tipo de ataque onde um site malicioso pode fazer o navegador do usuário enviar uma solicitação para o seu site.
Isso pode ser usado para executar ações no seu site sem o conhecimento do usuário. O Flight não fornece um mecanismo de proteção CSRF
integrado, mas você pode implementar facilmente o seu próprio usando middleware.

#### Configuração

Primeiro, você precisa gerar um token CSRF e armazená-lo na sessão do usuário. Você pode então usar esse token em seus formulários e verificá-lo quando
o formulário for enviado. Usaremos o plugin [flightphp/session](/awesome-plugins/session) para gerenciar as sessões.

```php
// Gera um token CSRF e o armazena na sessão do usuário
// (supondo que você criou um objeto de sessão e o anexou ao Flight)
// consulte a documentação de sessão para mais informações
Flight::register('session', flight\Session::class);

// Você só precisa gerar um único token por sessão (para que funcione
// em várias abas e solicitações para o mesmo usuário)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando o Template PHP Padrão do Flight

```html
<!-- Use o token CSRF no seu formulário -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- outros campos do formulário -->
</form>
```

##### Usando Twig (padrão do skeleton)

Registre uma função Twig ou passe o token para cada visualização de formulário. Exemplo mínimo com uma global e campo de formulário:

```php
// Ao configurar o Twig (ex: services.php)
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

E agora, nos seus templates Latte, você pode usar a função `csrf()` para exibir o token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- outros campos do formulário -->
</form>
```

#### Verificando o Token CSRF

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

##### Filtros de Evento

```php
// Este middleware verifica se a solicitação é POST e, se for, verifica se o token CSRF é válido
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
de valores de formulários que seus usuários finais preencherão. Você **nunca** deve confiar na saída dos seus usuários! Sempre assuma que todos eles são os
melhores hackers do mundo. Eles podem injetar JavaScript ou HTML malicioso na sua página. Esse código pode ser usado para roubar informações dos seus
usuários ou executar ações no seu site. Usando a classe de visão do Flight ou um mecanismo de template como [Twig](/awesome-plugins/twig) ou [Latte](/awesome-plugins/latte), você pode facilmente escapar a saída para prevenir ataques XSS.

```php
// Vamos supor que o usuário é esperto e tenta usar isso como nome
$name = '<script>alert("XSS")</script>';

// Isso vai escapar a saída
Flight::view()->set('name', $name);
// Isso vai exibir: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (padrão do skeleton) e Latte escapam automaticamente por padrão — prefira-os em vez de echo PHP puro
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escapado
// Evite |raw / saída sem escape, a menos que o conteúdo seja totalmente confiável
```

### Injeção de SQL

Injeção de SQL é um tipo de ataque onde um usuário malicioso pode injetar código SQL no seu banco de dados. Isso pode ser usado para roubar informações
do seu banco de dados ou executar ações no seu banco de dados. Novamente, você **nunca** deve confiar na entrada dos seus usuários! Sempre assuma que eles estão
atrás de sangue. Use declarações preparadas—os helpers do [SimplePdo](/learn/simple-pdo) tornam esse o caminho padrão.

```php
// Supondo que você tenha Flight::db() registrado como SimplePdo (ou injete SimplePdo no controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (preferido) — linhas únicas com parâmetros vinculados
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Mesma ideia com placeholders ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Em controllers no estilo do skeleton, prefira a injeção de `SimplePdo` pelo construtor em vez de `Flight::db()` para que testes e código gerado por IA permaneçam consistentes ([DIC](/learn/dependency-injection-container)).

#### Exemplo Inseguro

Abaixo está o motivo pelo qual usamos declarações SQL preparadas para proteger contra exemplos inocentes como o seguinte:

```php
// o usuário final preenche um formulário web.
// para o valor do formulário, o hacker coloca algo assim:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Após a consulta ser montada, ela fica assim
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Parece estranho, mas é uma consulta válida que vai funcionar. Na verdade,
// é um ataque de injeção de SQL muito comum que retornará todos os usuários.

var_dump($users); // isso vai exibir todos os usuários no banco de dados, não apenas o único nome de usuário
```

### Segredos e configuração

- Coloque segredos em **`.env`** (ou no ambiente real), não em amostras de `config.php` commitadas.
- Regra do skeleton: padrões literais em `config.php`; mescle o env no bootstrap; **não** leia `$_ENV` dentro dos controllers—injete a configuração em vez disso. Consulte [Configuração](/learn/configuration).
- Nunca faça commit de chaves de API, senhas de banco de dados ou chaves de criptografia de sessão. Aponte ferramentas de IA para **`SECURITY.md`** para que elas não inventem atalhos inseguros.

### Validação de Callback JSONP

Se você usar o método `Flight::jsonp()`, esteja ciente de que o Flight valida o nome do parâmetro de callback JSONP contra uma regex de lista de permissões estrita (`/^[A-Za-z_$][\w$.]{0,127}$/`). Qualquer nome de callback que não corresponda a esse padrão fará o Flight lançar uma exceção, prevenindo a injeção de JavaScript arbitrário através de um valor de callback malicioso.

Essa validação é integrada e não requer configuração adicional, mas é bom saber disso ao depurar erros inesperados de endpoints JSONP.

### CORS

Cross-Origin Resource Sharing (CORS) é um mecanismo que permite que muitos recursos (ex: fontes, JavaScript, etc.) em uma página web sejam
solicitados de outro domínio fora do domínio do qual o recurso se originou. O Flight não possui funcionalidade integrada,
mas isso pode ser facilmente tratado com um hook para executar antes que o método `Flight::start()` seja chamado.

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

// bootstrap / routes — execute antes de start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Endurecimento da Configuração do Flight

O Flight expõe várias configurações do mecanismo que têm implicações diretas de segurança. Configurá-las corretamente é uma das maneiras mais fáceis de endurecer sua aplicação.

#### `flight.allow_method_override`

Por padrão, o Flight permite que os clientes substituam o método HTTP de uma solicitação usando o cabeçalho `X-HTTP-Method-Override` ou um campo `_method` no corpo de um POST. Embora isso seja útil para formulários HTML que só podem enviar `GET`/`POST`, pode ser perigoso se você não estiver esperando isso — um atacante pode forjar solicitações `DELETE` ou `PUT` através de um formulário comum.

Se sua aplicação não depende desse comportamento (ex: você está construindo uma API consumida por clientes modernos ou frontends JavaScript que podem enviar qualquer verbo HTTP), você deve desativá-lo:

```php
// No seu index.php ou arquivo de bootstrap, antes de Flight::start()
Flight::set('flight.allow_method_override', false);
```

O valor padrão é `true` para compatibilidade reversa, mas **defini-lo como `false` é fortemente recomendado** para qualquer aplicação que não precise explicitamente do recurso de substituição.

#### `flight.debug`

O Flight tem uma configuração `flight.debug` que controla se informações detalhadas de erro (mensagem de exceção, código e rastreamento de pilha completo) são renderizadas no navegador quando uma exceção não tratada ocorre. O padrão é `false`, o que significa que apenas uma mensagem genérica de `500 Internal Server Error` é exibida — nenhum detalhe interno é vazado para o cliente.

Nunca ative isso em um servidor de produção. Use apenas localmente ou em um ambiente de staging:

```php
// Seguro apenas para desenvolvimento local — NUNCA em produção
Flight::set('flight.debug', true);
```

Quando `flight.debug` é `false` (o padrão), você ainda pode capturar erros ativando `flight.log_errors`:

```php
// Registra erros no servidor sem expô-los ao cliente
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

A classe `View` integrada do Flight incluirá felizmente um template de um caminho absoluto, ou de um nome relativo que saia de `flight.views.path` (por exemplo, com `../`). Isso é intencional para aplicativos que deliberadamente compartilham templates entre pastas, mas também é um risco de path traversal se um nome de template vier de entrada não confiável.

`flight.views.restrict_to_path` está **desativado por padrão** para que aplicativos existentes continuem funcionando. Ative-o a menos que você tenha um motivo documentado para não fazê-lo:

```php
// No seu index.php ou arquivo de bootstrap, antes de Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

O mecanismo aplica essa configuração em `View::$restrictToPath` quando a visão é criada (mesmo padrão de `flight.views.path` e `flight.views.extension`). Com ele ativado:

- `render()` e `fetch()` só incluem arquivos cujo caminho real está dentro do diretório de visões configurado (symlinks que apontam para fora também são rejeitados).
- `exists()` retorna `false` para esses mesmos caminhos em vez de lançar uma exceção.
- `getTemplate()` em si não é alterado — ele continua retornando caminhos como sempre fez.
- Um arquivo bloqueado lança `Template file is outside the views path.` Um arquivo ausente ainda lança a mensagem existente de `Template file not found: ...`.

Se você usa Twig ou Latte com seus próprios carregadores de sistema de arquivos apontados para seu diretório de visões, esses mecanismos já contêm templates nessa raiz. Mesmo assim, ative isso para a `View` nativa do Flight para que qualquer código que chame `Flight::view()->render()` / `fetch()` receba a mesma proteção. O [skeleton](https://github.com/flightphp/skeleton) oficial ativa isso no bootstrap.

#### Configuração de produção recomendada

```php
// index.php ou aplicado a partir da configuração do app / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Tratamento de Erros
Oculte detalhes sensíveis de erro em produção para evitar vazar informações para atacantes. Em produção, registre erros em vez de exibi-los com `display_errors` definido como `0`.

```php
// No seu bootstrap.php ou index.php

// adicione isso ao seu app/config/config.php
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

### Sanitização de Entrada
Nunca confie na entrada do usuário. Sanitize-a usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes de processar para prevenir que dados maliciosos entrem. Prefira ler a entrada via `$app->request()` (ou `Flight::request()`) em vez de `$_GET` / `$_POST` brutos no código do aplicativo.

```php

// Vamos supor uma solicitação $_POST com $_POST['input'] e $_POST['email']

// Sanitiza uma entrada de string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitiza um email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hash de Senhas
Armazene senhas com segurança e verifique-as com segurança usando as funções integradas do PHP como [password_hash](https://www.php.net/manual/en/function.password-hash.php) e [password_verify](https://www.php.net/manual/en/function.password-verify.php). As senhas nunca devem ser armazenadas em texto puro, nem devem ser criptografadas com métodos reversíveis. O hash garante que, mesmo que seu banco de dados seja comprometido, as senhas reais permaneçam protegidas.

```php
$password = Flight::request()->data->password;
// Gera o hash de uma senha ao armazená-la (ex: durante o registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica uma senha (ex: durante o login)
if (password_verify($password, $stored_hash)) {
    // A senha corresponde
}
```

### Limitação de Taxa
Proteja contra ataques de força bruta ou ataques de negação de serviço limitando as taxas de solicitação com um cache.

```php
// Supondo que você tenha flightphp/cache instalado e registrado
// Usando flightphp/cache em um filtro
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reinicia após 60 segundos
});
```

## Veja Também
- [Sessões](/awesome-plugins/session) - Como gerenciar sessões de usuário com segurança.
- [Templates](/learn/templates) - Auto-escape do Twig/Latte e XSS.
- [SimplePdo](/learn/simple-pdo) - Helpers de banco de dados com declarações preparadas.
- [PdoWrapper](/learn/pdo-wrapper) - Obsoleto; use SimplePdo para código novo.
- [Middleware](/learn/middleware) - Como usar middleware para simplificar o processo de adicionar cabeçalhos de segurança.
- [Configuração](/learn/configuration) - `.env` vs configuração literal, flags de produção.
- [IA e Experiência do Desenvolvedor](/learn/ai) - Mantenha a política de segurança em `SECURITY.md` para agentes.
- [Respostas](/learn/responses) - Como personalizar respostas HTTP com cabeçalhos seguros.
- [Solicitações](/learn/requests) - Como lidar e sanitizar a entrada do usuário.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Função PHP para sanitização de entrada.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Função PHP para hash seguro de senhas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Função PHP para verificar senhas com hash.

## Solução de Problemas
- Consulte a seção "Veja Também" acima para obter informações de solução de problemas relacionadas a problemas com componentes do Framework Flight.
- Se o CSP bloquear seus scripts, adicione um nonce (padrão do skeleton) ou coloque origens específicas na lista de permissões—não defina `script-src *` sem um plano.

## Changelog
- Documentação – `App\Middleware` do skeleton, notas de CSRF/XSS do Twig, SimplePdo, segredos/`.env` e `SECURITY.md` para projetos compatíveis com IA.
- Documentação – Documentado `flight.views.restrict_to_path` sob Endurecimento da Configuração do Flight (contenção de caminho opt-in para visões nativas).
- v3.18.1 - Adicionada a seção Endurecimento da Configuração do Flight cobrindo `flight.allow_method_override`, `flight.debug` e validação de callback JSONP.
- v3.1.0 - Adicionadas seções sobre CORS, Tratamento de Erros, Sanitização de Entrada, Hash de Senhas e Limitação de Taxa.
- v2.0 - Adicionado escape para visões padrão para prevenir XSS.